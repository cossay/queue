<?php
declare(strict_types = 1);
namespace Cosman\Queue\Runner;

use Cosman\Queue\Http\Response\Response;
use Cosman\Queue\Store\Model\Job;
use Cosman\Queue\Store\Model\Output;
use Cosman\Queue\Store\Repository\TaskRepositoryInterface;
use Cosman\Queue\Support\DateTime\DateTime;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Promise\PromiseInterface;
use function GuzzleHttp\Promise\settle;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Request;
use GuzzleHttp\Exception\RequestException;

/**
 *
 * @author cosman
 *        
 */
class JobRunner
{

    /**
     *
     * @var Client
     */
    protected $httpClient;

    /**
     *
     * @var TaskRepositoryInterface
     */
    protected $repository;

    /**
     *
     * @param Client $httpClient
     * @param TaskRepositoryInterface $repository
     */
    public function __construct(Client $httpClient, TaskRepositoryInterface $repository)
    {
        $this->httpClient = $httpClient;
        
        $this->repository = $repository;
    }

    /**
     * Returns all waiting jobs
     *
     * @return \Cosman\Queue\Store\Model\Job[]
     */
    protected function fetchWaitingJobs(): iterable
    {
        return $this->repository->read(1000, 0);
    }

    /**
     * Composes a request for a job
     *
     * @param Job $job
     * @return PromiseInterface
     */
    protected function composeRequest(Job $job): PromiseInterface
    {
        $promise = null;
        
        switch ($job->getRequestMethod()) {
            case Request::METHOD_POST:
                $promise = $this->httpClient->postAsync($job->getCallbackUrl(), array(
                    'headers' => $job->getHeaders(),
                    'json' => $job->getPayload()
                ));
                break;
            case Request::METHOD_PUT:
                $promise = $this->httpClient->putAsync($job->getCallbackUrl(), array(
                    'headers' => $job->getHeaders(),
                    'json' => $job->getPayload()
                ));
                break;
            case Request::METHOD_DELETE:
                $promise = $this->httpClient->deleteAsync($job->getCallbackUrl(), array(
                    'headers' => $job->getHeaders(),
                    'query' => json_encode($job->getPayload())
                ));
                break;
            default:
                $promise = $this->httpClient->getAsync($job->getCallbackUrl(), array(
                    'headers' => $job->getHeaders(),
                    'query' => json_encode($job->getPayload())
                ));
        }
        
        return $promise;
    }

    /**
     * Processes job request responses
     *
     * @param array $responses
     * @param array $originalJobs
     */
    protected function processTaskOutputs(array $responses, array &$originalJobs)
    {
        $outputs = [];
        $jobs = [];
        
        foreach ($responses as $jobCode => $response) {
            
            if ($response instanceof ResponseInterface) {
                
                $job = $originalJobs[$jobCode];
                
                if (! ($job instanceof Job) || 0 == $job->getRetries()) {
                    continue;
                }
                
                $job->setTriedCounts($job->getTriedCounts() + 1);
                $job->setIsExecuted(true);
                $job->setIsSuccessful(Response::HTTP_OK === $response->getStatusCode());
                
                if (($job->getRetries() > $job->getTriedCounts()) && ! $job->isSuccessful()) {
                    $next_execution = new DateTime(sprintf('+%d seconds', $job->getRetryDelay()));
                    $job->setNextExecution($next_execution);
                }
                
                $output = new Output();
                $output->setJob($job);
                $output->setContent($response->getBody()
                    ->getContents());
                $output->setStatusCode($response->getStatusCode());
                $output->setStatusMessage($response->getReasonPhrase());
                $output->setHeaders($response->getHeaders());
                
                $outputs[] = $output;
                $jobs[] = $job;
            }
        }
        
        if ($this->repository->writeOutputs(...$outputs)) {
            $this->repository->update(...$jobs);
            echo sprintf('%d job(s) processed at %s.', count($outputs), (new \DateTime())->format(\DateTime::W3C)) . PHP_EOL;
        }
    }

    /**
     * Monitors an runs waiting jobs
     *
     * @param int $sleep
     * @param int $batchSize
     */
    public function run(int $sleep = 2, int $batchSize = 200): void
    {
        echo 'WAITING FOR JOBS...' . PHP_EOL;
        
        while (true) {
            
            $waitingJobs = $jobsCodeAsKey = [];
            
            foreach ($this->fetchWaitingJobs() as $job) {
                if ($job instanceof Job) {
                    $waitingJobs[] = $job;
                    $jobsCodeAsKey[$job->getCode()] = $job;
                }
            }
            
            if (count($jobsCodeAsKey)) {
                
                $batches = array_chunk($jobsCodeAsKey, $batchSize, true);
                
                foreach ($batches as $batch) {
                    
                    $responses = $this->compileTasks($batch)->wait();
                    
                    $decodedResponse = $this->decodeResponses($responses);
                    
                    $this->processTaskOutputs($decodedResponse, $batch);
                    
                    unset($responses, $decodedResponse, $batch);
                }
                
                $this->repository->release(...$waitingJobs);
                
                unset($batches);
            }
            
            unset($waitingJobs, $jobsCodeAsKey);
            
            sleep($sleep);
        }
    }

    /**
     * Transforms a collection of jobs into request promises for execution
     *
     * @param array $jobs
     * @return PromiseInterface
     */
    protected function compileTasks(array $jobs): PromiseInterface
    {
        $promises = [];
        
        foreach ($jobs as $job) {
            if ($job instanceof Job) {
                $promises[$job->getCode()] = $this->composeRequest($job);
            }
        }
        
        return settle($promises);
    }

    /**
     * Decodes a collection of responses
     *
     * @param iterable $responses
     * @return \Psr\Http\Message\ResponseInterface[]
     */
    protected function decodeResponses(iterable $responses): iterable
    {
        $resolved = [];
        
        foreach ($responses as $jobCode => $response) {
            // Request suceeded
            if ($response['state'] == 'fulfilled') {
                $resolved[$jobCode] = $response['value'] ?? null;
            } else {
                // Request failed
                $exception = $response['reason'] ?? null;
                
                $dummyResponseCode = $exception->getCode() ? $exception->getCode() : Response::HTTP_INTERNAL_SERVER_ERROR;
                $dummyResponseMessage = $exception->getMessage();
                
                if ($exception instanceof RequestException) {
                    $dummyResponseMessage = 'Queue server exception: Connection problem: ' . $exception->getMessage();
                }
                
                $dummyResponse = new \GuzzleHttp\Psr7\Response($dummyResponseCode, [], null, '1.1', $dummyResponseMessage);
                
                if ($exception instanceof RequestException || $exception instanceof ClientException || $exception instanceof ServerException && $exception->hasResponse()) {
                    $resolved[$jobCode] = $exception->getResponse() ? $exception->getResponse() : $dummyResponse;
                }
            }
        }
        
        return $resolved;
    }
}