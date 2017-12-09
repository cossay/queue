<?php
declare(strict_types = 1);
namespace Cosman\Queue\Store\Repository;

use Cosman\Queue\Store\Model\BaseModel;
use Cosman\Queue\Store\Model\Output;
use Cosman\Queue\Store\Model\Job;
use Cosman\Queue\Store\Table\JobTable;
use Cosman\Queue\Store\Table\OutputTable;
use Cosman\Queue\Support\DateTime\DateTime;
use Exception;

/**
 *
 * @author cosman
 *        
 */
class TaskRepository extends BaseRepository implements TaskRepositoryInterface
{

    /**
     *
     * {@inheritdoc}
     * @see \Cosman\Queue\Store\Repository\TaskRepositoryInterface::read()
     */
    public function read(int $limit = 1000, int $offset = 0): iterable
    {
        $tasks = [];
        
        try {
            $this->connection->beginTransaction();
            
            $query = $this->connection->table(JobTable::NAME)
                ->limit($limit)
                ->offset($offset);
            
            $query->where(JobTable::FIELD_IS_SUCCESSFUL, '<>', JobTable::BOOLEAN_TRUE);
            
            $query->where(JobTable::FIELD_IS_PROCESSING, '<>', JobTable::BOOLEAN_TRUE);
            
            $query->whereRaw(sprintf('%s > %s', JobTable::FIELD_RETRIES, JobTable::FIELD_RETRY_COUNTS));
            
            $query->where(JobTable::FIELD_NEXT_EXECUTION, '<=', new DateTime())->lockForUpdate();
            
            $tasks = $this->formatCollection($query->get());
            
            $this->hold(...$tasks);
            
            $this->connection->commit();
        } catch (Exception $e) {
            $this->connection->rollBack();
            
            throw $e;
        }
        
        return $tasks;
    }

    /**
     *
     * {@inheritdoc}
     * @see \Cosman\Queue\Store\Repository\BaseRepository::format()
     */
    protected function format($model, array $relations = []): ?BaseModel
    {
        return Job::createInstance($model);
    }

    /**
     *
     * {@inheritdoc}
     * @see \Cosman\Queue\Store\Repository\TaskRepositoryInterface::writeOutputs()
     */
    public function writeOutputs(Output ...$outputs): bool
    {
        $attributes = [];
        
        $now = new DateTime();
        
        foreach ($outputs as $output) {
            
            $code = strtoupper(sha1(sprintf('%s-%s-%s', microtime(), $output->getJob()->getCode(), rand())));
            
            $attributes[] = array(
                OutputTable::FIELD_CODE => $code,
                OutputTable::FIELD_JOB_ID => $output->getJob()->getId(),
                OutputTable::FIELD_CONTENT => json_encode($output->getContent()),
                OutputTable::FIELD_STATUS_CODE => $output->getStatusCode(),
                OutputTable::FIELD_STATUS_MESSAGE => $output->getStatusMessage(),
                OutputTable::FIELD_HEADERS => json_encode($output->getHeaders()),
                OutputTable::FIELD_CREATED_AT => $now,
                OutputTable::FIELD_UPDATED_AT => null
            );
        }
        
        return $this->connection->table(OutputTable::NAME)->insert($attributes);
    }

    /**
     *
     * {@inheritdoc}
     * @see \Cosman\Queue\Store\Repository\TaskRepositoryInterface::update()
     */
    public function update(Job ...$jobs): int
    {
        $affectedRows = 0;
        
        try {
            $this->connection->beginTransaction();
            
            foreach ($jobs as $job) {
                if ($job->getId()) {
                    $attributes = array(
                        JobTable::FIELD_IS_EXECUTED => $job->isExecuted() ? JobTable::BOOLEAN_TRUE : JobTable::BOOLEAN_FALSE,
                        JobTable::FIELD_IS_SUCCESSFUL => $job->isSuccessful() ? JobTable::BOOLEAN_TRUE : JobTable::BOOLEAN_FALSE,
                        JobTable::FIELD_RETRY_COUNTS => $job->getTriedCounts(),
                        JobTable::FIELD_NEXT_EXECUTION => $job->getNextExecution(),
                        JobTable::FIELD_UPDATED => new DateTime()
                    );
                    
                    $affectedRows += $this->connection->table(JobTable::NAME)
                        ->where(JobTable::FIELD_ID, '=', $job->getId())
                        ->update($attributes);
                }
            }
            
            $this->connection->commit();
        } catch (Exception $e) {
            $this->connection->rollBack();
            
            throw $e;
        }
        
        return $affectedRows;
    }

    /**
     *
     * {@inheritdoc}
     * @see \Cosman\Queue\Store\Repository\TaskRepositoryInterface::release()
     */
    public function release(Job ...$jobs): int
    {
        $codes = [];
        
        foreach ($jobs as $job) {
            if ($job->getCode()) {
                $codes[] = $job->getCode();
            }
        }
        
        if (! count($codes)) {
            return 0;
        }
        
        $attributes = array(
            JobTable::FIELD_IS_PROCESSING => JobTable::BOOLEAN_FALSE,
            JobTable::FIELD_UPDATED => new DateTime()
        );
        
        return $this->connection->table(JobTable::NAME)
            ->whereIn(JobTable::FIELD_CODE, $codes)
            ->update($attributes);
    }

    /**
     * Marks a collection of jobs as being processed
     *
     * @param Job ...$jobs
     * @return int
     */
    protected function hold(Job ...$jobs): int
    {
        $codes = [];
        
        foreach ($jobs as $job) {
            if ($job->getCode()) {
                $codes[] = $job->getCode();
            }
        }
        
        if (! count($codes)) {
            return 0;
        }
        
        $attributes = array(
            JobTable::FIELD_IS_PROCESSING => JobTable::BOOLEAN_TRUE,
            JobTable::FIELD_UPDATED => new DateTime()
        );
        
        return $this->connection->table(JobTable::NAME)
            ->whereIn(JobTable::FIELD_CODE, $codes)
            ->update($attributes);
    }

    /**
     *
     * {@inheritdoc}
     * @see \Cosman\Queue\Store\Repository\BaseRepository::getSelectableFields()
     */
    protected function getSelectableFields(): array
    {
        return JobTable::definition()->getFields()->all();
    }
}