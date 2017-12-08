<?php
declare(strict_types = 1);
namespace Cosman\Queue\Store\Repository;

use Cosman\Queue\Store\Model\ {
    Client,
    Job
};
use Cosman\Queue\Store\Model\Project;

/**
 * Job repository interface
 *
 * @author cosman
 *        
 */
interface JobRepositoryInterface
{

    /**
     * Counts number of jobs available
     *
     * If a client is provided, only jobs created by the client will be counted
     *
     * @param Client $client
     * @param Project $project
     * @return int
     */
    public function count(Client $client = null, Project $project = null): int;

    /**
     * Fetches a number of jobs
     *
     * If a client is provided, only jobs created by the client will be returns
     *
     * @param int $limit
     * @param int $offset
     * @param Client $client
     * @param Project $project
     * @return \Cosman\Queue\Store\Model\Job[]
     */
    public function fetch(int $limit, int $offset, Client $client = null, Project $project = null): iterable;

    /**
     * Fetches a single job by Id
     *
     * If client is provided, only a matching job belonging to client will returned
     *
     * @param int $id
     * @param Client $client
     * @param Project $project
     * @return \Cosman\Queue\Store\Model\Job|NULL
     */
    public function fetchById(int $id, Client $client = null, Project $project = null): ?Job;

    /**
     * Fetches a single job by code
     *
     * If client is provided, only a matching job belonging to client will returned
     *
     * @param string $code
     * @param Client $client
     * @param Project $project
     * @return \Cosman\Queue\Store\Model\Job|NULL
     */
    public function fetchByCode(string $code, Client $client = null, Project $project = null): ?Job;

    /**
     * Returns number of waiting jobs
     *
     * @param Client $client
     * @param Project $project
     * @return int
     */
    public function countWaitingJobs(Client $client = null, Project $project = null): int;

    /**
     * Fetches a number of waiting jobs
     *
     * @param int $limit
     * @param int $offset
     * @param Client $client
     * @param Project $project
     * @return iterable
     */
    public function fetchWaitingJobs(int $limit, int $offset, Client $client = null, Project $project = null): iterable;

    /**
     * Creates a single job
     *
     * @param Job $job
     * @return int Unique Id of newly created job
     */
    public function create(Job $job): int;

    /**
     * Creates at least one job
     * 
     * @param Job ...$jobs
     * @return bool
     */
    public function createMany(Job ...$jobs): bool;

    /**
     * Updates a collection of jobs
     *
     * @param Job ...$jobs
     * @return int Number of jobs updated
     */
    public function update(Job ...$jobs): int;

    /**
     * Deletes a collection of jobs
     *
     * @param Job ...$jobs
     * @return int Number of jobs deleted
     */
    public function delete(Job ...$jobs): int;
}