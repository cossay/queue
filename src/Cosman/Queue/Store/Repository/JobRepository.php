<?php
declare(strict_types = 1);
namespace Cosman\Queue\Store\Repository;

use Cosman\Queue\Store\Model\BaseModel;
use Cosman\Queue\Store\Model\Client;
use Cosman\Queue\Store\Model\Job;
use Cosman\Queue\Store\Model\Project;
use Cosman\Queue\Store\Table\ClientTable;
use Cosman\Queue\Store\Table\JobTable;
use Cosman\Queue\Store\Table\ProjectTable;
use Cosman\Queue\Support\DateTime\DateTime;
use Illuminate\Database\Query\Builder;

/**
 * Job repository
 *
 * @author cosman
 *        
 */
class JobRepository extends BaseRepository implements JobRepositoryInterface
{

    const RELATION_CLIENT = 'job_client_tb';

    const RELATION_PROJECT = 'job_project_tb';

    /**
     *
     * @param array $selectableFields
     * @param Client $client
     * @param Project $project
     * @return Builder
     */
    protected function withJoins(array $selectableFields = [], Client $client = null, Project $project = null): Builder
    {
        if (empty($selectableFields)) {
            $selectableFields = $this->createSelectableFieldList();
        }
        
        $query = $this->capsule->table(JobTable::NAME)
            ->leftJoin(ProjectTable::NAME, ProjectTable::FIELD_ID, '=', JobTable::FIELD_PROJECT_ID)
            ->leftJoin(ClientTable::NAME, ClientTable::FIELD_ID, '=', ProjectTable::FIELD_CLIENT_ID);
        
        if (! empty($selectableFields)) {
            $query->select($selectableFields);
        }
        
        if (null !== $client) {
            $query->where(ProjectTable::FIELD_CLIENT_ID, '=', $client->getId());
        }
        
        if (null !== $project) {
            $query->where(JobTable::FIELD_PROJECT_ID, '=', $project->getId());
        }
        
        return $query;
    }

    /**
     *
     * {@inheritdoc}
     * @see \Cosman\Queue\Store\Repository\JobRepositoryInterface::count()
     */
    public function count(Client $client = null, Project $project = null): int
    {
        return $this->withJoins([], $client, $project)->count();
    }

    /**
     *
     * {@inheritdoc}
     * @see \Cosman\Queue\Store\Repository\JobRepositoryInterface::fetch()
     */
    public function fetch(int $limit, int $offset, Client $client = null, Project $project = null): iterable
    {
        $query = $this->withJoins([], $client, $project)
            ->limit($limit)
            ->offset($offset);
        
        return $this->formatCollection($query->get());
    }

    /**
     *
     * {@inheritdoc}
     * @see \Cosman\Queue\Store\Repository\JobRepositoryInterface::fetchById()
     */
    public function fetchById(int $id, Client $client = null, Project $project = null): ?Job
    {
        $query = $this->withJoins([], $client, $project);
        
        $query->where(JobTable::FIELD_ID, '=', $id);
        
        return $this->format($query->first());
    }

    /**
     *
     * {@inheritdoc}
     * @see \Cosman\Queue\Store\Repository\JobRepositoryInterface::fetchByCode()
     */
    public function fetchByCode(string $code, Client $client = null, Project $project = null): ?Job
    {
        $query = $this->withJoins([], $client, $project);
        
        $query->where(JobTable::FIELD_CODE, '=', $code);
        
        return $this->format($query->first());
    }

    /**
     *
     * {@inheritdoc}
     * @see \Cosman\Queue\Store\Repository\JobRepositoryInterface::countWaitingJobs()
     */
    public function countWaitingJobs(Client $client = null, Project $project = null): int
    {
        $query = $this->withJoins([], $client, $project);
        
        $query->whereRaw(sprintf('%s != %s', JobTable::FIELD_RETRIES, JobTable::FIELD_RETRY_COUNTS));
        
        $query->where(JobTable::FIELD_NEXT_EXECUTION, '<=', new DateTime());
        
        return $query->count();
    }

    /**
     *
     * {@inheritdoc}
     * @see \Cosman\Queue\Store\Repository\JobRepositoryInterface::fetchWaitingJobs()
     */
    public function fetchWaitingJobs(int $limit, int $offset, Client $client = null, Project $project = null): iterable
    {
        $query = $this->withJoins([], $client, $project)
            ->limit($limit)
            ->offset($offset);
        
        $query->whereRaw(sprintf('%s > %s', JobTable::FIELD_RETRIES, JobTable::FIELD_RETRY_COUNTS));
        
        $query->where(JobTable::FIELD_NEXT_EXECUTION, '<=', new DateTime());
        
        return $this->formatCollection($query->get());
    }

    /**
     *
     * {@inheritdoc}
     * @see \Cosman\Queue\Store\Repository\JobRepositoryInterface::create()
     */
    public function create(Job $job): int
    {
        $code = strtoupper(sha1(sprintf('%s-%s', microtime(), $job->getProject()->getCode())));
        
        $attributes = array(
            JobTable::FIELD_CODE => $code,
            JobTable::FIELD_PROJECT_ID => $job->getProject()->getId(),
            JobTable::FIELD_IS_EXECUTED => Job::STATUS_NOT_EXECUTED,
            JobTable::FIELD_TITLE => $job->getTitle(),
            JobTable::FIELD_DESCRIPTION => $job->getDescription(),
            JobTable::FIELD_DELAY => $job->getDelay(),
            JobTable::FIELD_RETRIES => $job->getRetries(),
            JobTable::FIELD_RETRY_DELAY => $job->getRetryDelay(),
            JobTable::FIELD_RETRY_COUNTS => $job->getTriedCounts(),
            JobTable::FIELD_CALLBACK_URL => $job->getCallbackUrl(),
            JobTable::FIELD_REQUEST_METHOD => $job->getRequestMethod(),
            JobTable::FIELD_PAYLOAD => json_encode($job->getPayload()),
            JobTable::FIELD_HEADERS => json_encode($job->getHeaders()),
            JobTable::FIELD_NEXT_EXECUTION => new DateTime(sprintf('+%d seconds', $job->getDelay())),
            JobTable::FIELD_CREATED_AT => new DateTime(),
            JobTable::FIELD_UPDATED => null
        );
        
        return $this->capsule->table(JobTable::NAME)->insertGetId($attributes);
    }

    /**
     *
     * {@inheritdoc}
     * @see \Cosman\Queue\Store\Repository\JobRepositoryInterface::createMany()
     */
    public function createMany(Job ...$jobs): bool
    {
        $now = new DateTime();
        
        $jobArray = [];
        
        foreach ($jobs as $job) {
            
            $code = strtoupper(sha1(sprintf('%s-%s-%s', microtime(), $job->getProject()->getCode(), rand())));
            
            $jobArray[] = array(
                JobTable::FIELD_CODE => $code,
                JobTable::FIELD_PROJECT_ID => $job->getProject()->getId(),
                JobTable::FIELD_IS_EXECUTED => Job::STATUS_NOT_EXECUTED,
                JobTable::FIELD_TITLE => $job->getTitle(),
                JobTable::FIELD_DESCRIPTION => $job->getDescription(),
                JobTable::FIELD_DELAY => $job->getDelay(),
                JobTable::FIELD_RETRIES => $job->getRetries(),
                JobTable::FIELD_RETRY_DELAY => $job->getRetryDelay(),
                JobTable::FIELD_RETRY_COUNTS => $job->getTriedCounts(),
                JobTable::FIELD_CALLBACK_URL => $job->getCallbackUrl(),
                JobTable::FIELD_REQUEST_METHOD => $job->getRequestMethod(),
                JobTable::FIELD_PAYLOAD => json_encode($job->getPayload()),
                JobTable::FIELD_HEADERS => json_encode($job->getHeaders()),
                JobTable::FIELD_NEXT_EXECUTION => new DateTime(sprintf('+%d seconds', $job->getDelay())),
                JobTable::FIELD_CREATED_AT => $now,
                JobTable::FIELD_UPDATED => null
            );
        }
        
        return $this->capsule->table(JobTable::NAME)->insert($jobArray);
    }

    /**
     *
     * {@inheritdoc}
     * @see \Cosman\Queue\Store\Repository\JobRepositoryInterface::update()
     */
    public function update(Job ...$jobs): int
    {
        $connection = $this->capsule->connection();
        
        try {
            
            $affectedRows = 0;
            
            foreach ($jobs as $job) {
                if ($job->getId()) {
                    $attributes = array(
                        JobTable::FIELD_PROJECT_ID => $job->getProject()->getId(),
                        JobTable::FIELD_IS_EXECUTED => $job->isExecuted() ? Job::STATUS_EXECUTED : Job::STATUS_NOT_EXECUTED,
                        JobTable::FIELD_TITLE => $job->getTitle(),
                        JobTable::FIELD_DESCRIPTION => $job->getDescription(),
                        JobTable::FIELD_DELAY => $job->getDelay(),
                        JobTable::FIELD_RETRY_DELAY => $job->getRetryDelay(),
                        JobTable::FIELD_RETRIES => $job->getRetries(),
                        JobTable::FIELD_RETRY_COUNTS => $job->getTriedCounts(),
                        JobTable::FIELD_CALLBACK_URL => $job->getCallbackUrl(),
                        JobTable::FIELD_REQUEST_METHOD => $job->getRequestMethod(),
                        JobTable::FIELD_PAYLOAD => json_encode($job->getPayload()),
                        JobTable::FIELD_HEADERS => json_encode($job->getHeaders()),
                        JobTable::FIELD_NEXT_EXECUTION => $job->getNextExecution(),
                        JobTable::FIELD_UPDATED => new DateTime()
                    );
                    
                    $this->capsule->table(JobTable::NAME)
                        ->where(JobTable::FIELD_ID, '=', $job->getId())
                        ->update($attributes);
                    
                    $affectedRows ++;
                }
            }
            
            $connection->commit();
            
            return $affectedRows;
        } catch (\Exception $e) {
            $connection->rollBack();
            
            throw $e;
        }
    }

    /**
     *
     * {@inheritdoc}
     * @see \Cosman\Queue\Store\Repository\JobRepositoryInterface::delete()
     */
    public function delete(Job ...$jobs): int
    {
        $ids = [];
        
        foreach ($jobs as $job) {
            if ($job->getId()) {
                $ids[] = $job->getId();
            }
        }
        
        if (empty($ids)) {
            return 0;
        }
        
        return $this->capsule->table(JobTable::NAME)
            ->whereIn(JobTable::FIELD_ID, $ids)
            ->delete();
    }

    /**
     *
     * {@inheritdoc}
     * @see \Cosman\Queue\Store\Repository\BaseRepository::format()
     */
    public function format($model, array $relations = []): ?BaseModel
    {
        $job = Job::createInstance($model);
        
        if (! ($job instanceof Job)) {
            return null;
        }
        
        $project = Project::createInstance($model, static::RELATION_PROJECT);
        
        if ($project instanceof Project) {
            
            $client = Client::createInstance($model, static::RELATION_CLIENT);
            
            if ($client instanceof Client) {
                $project->setClient($client);
            }
            
            $job->setProject($project);
        }
        
        return $job;
    }

    /**
     *
     * {@inheritdoc}
     * @see \Cosman\Queue\Store\Repository\BaseRepository::getSelectableFields()
     */
    protected function getSelectableFields(): array
    {
        $fields = array(
            JobTable::definition()->getFields()->all(),
            ClientTable::definition()->aliasFields(static::RELATION_CLIENT)->all(),
            ProjectTable::definition()->aliasFields(static::RELATION_PROJECT)->all()
        );
        
        return array_merge(...$fields);
    }
}