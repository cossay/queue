<?php
declare(strict_types = 1);
namespace Cosman\Queue\Runner;

use Cosman\Queue\Store\Repository\JobRepositoryInterface;
use Cosman\Queue\Store\Repository\OutputRepositoryInterface;
use GuzzleHttp\Client;
use Cosman\Queue\Store\Model\Job;
use GuzzleHttp\Promise\PromiseInterface;
use function GuzzleHttp\Promise\settle;
use Symfony\Component\HttpFoundation\Request;
use Psr\Http\Message\ResponseInterface;
use Cosman\Queue\Store\Model\Output;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Cosman\Queue\Http\Response\Response;
use Cosman\Queue\Support\DateTime\DateTime;

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
     * @var JobRepositoryInterface
     */
    protected $jobRepository;

    /**
     *
     * @var OutputRepositoryInterface
     */
    protected $outputRepository;

    /**
     *
     * @param Client $httpClient
     * @param JobRepositoryInterface $jobRepository
     * @param OutputRepositoryInterface $outputRepository
     */
    public function __construct(Client $httpClient, JobRepositoryInterface $jobRepository, OutputRepositoryInterface $outputRepository)
    {
        $this->httpClient = $httpClient;
        
        $this->jobRepository = $jobRepository;
        
        $this->outputRepository = $outputRepository;
    }

    /**
     * Returns all waiting jobs
     *
     * @return \Cosman\Queue\Store\Model\Job[]
     */
    protected function fetchWaitingJobs(): iterable
    {
        return $this->jobRepository->fetchWaitingJobs(1000, 0);
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
    protected function processTaskOupts(array $responses, array &$originalJobs)
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
                
                if (($job->getRetries() > $job->getTriedCounts()) && $response->getStatusCode() != Response::HTTP_OK) {
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
        
        if ($this->outputRepository->createMany(...$outputs)) {
            $this->jobRepository->update(...$jobs);
            echo sprintf('%d outputs written to repository at %s.', count($outputs), (new \DateTime())->format(\DateTime::W3C)) . PHP_EOL;
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
        while (true) {
            
            $waitingJobs = [];
            
            foreach ($this->fetchWaitingJobs() as $job) {
                if ($job instanceof Job) {
                    $waitingJobs[$job->getCode()] = $job;
                }
            }
            
            if (count($waitingJobs)) {
                
                $batches = array_chunk($waitingJobs, $batchSize, true);
                
                foreach ($batches as $batch) {
                    $responses = $this->compileTasks($batch)->wait();
                    
                    $decodedResponse = $this->decodeResponses($responses);
                    
                    $this->processTaskOupts($decodedResponse, $batch);
                    
                    unset($responses, $decodedResponse, $batch);
                }
                
                unset($batches);
            }
            
            unset($waitingJobs);
            
            sleep($sleep);
        }
    }

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
                $resolved[$jobCode] = $response['value'];
            } else {
                // Request failed
                $exception = $response['reason'];
                
                if ($exception instanceof ClientException || $exception instanceof ServerException && $exception->hasResponse()) {
                    $resolved[$jobCode] = $exception->getResponse();
                }
            }
        }
        
        return $resolved;
    }
}