<?php
declare(strict_types = 1);
namespace Cosman\Queue\Store\Repository;

use Cosman\Queue\Store\Model\Job;
use Cosman\Queue\Store\Model\Output;
use Cosman\Queue\Store\Model\Client;
use Cosman\Queue\Store\Model\Project;

/**
 *
 * @author cosman
 *        
 */
interface OutputRepositoryInterface
{

    /**
     * Counts number of outputs available
     *
     * If a job is provided, only output created for the job will be counted
     *
     * @param Job $job
     * @param Client $client
     * @param Project $project
     * @return int
     */
    public function count(Job $job = null, Client $client = null, Project $project = null): int;

    /**
     * Fetches a number of outputs
     *
     * If a job is provided, only outputs created for the job will be returns
     *
     * @param int $limit
     * @param int $offset
     * @param Job $job
     * @param Client $client
     * @param Project $project
     * @return \Cosman\Queue\Store\Model\Output[]
     */
    public function fetch(int $limit, int $offset, Job $job = null, Client $client = null, Project $project = null): iterable;

    /**
     * Fetches a single output by Id
     *
     * If job is provided, only a matching output belonging to job will returned
     *
     * @param int $id
     * @param Job $job
     * @param Client $client
     * @param Project $project
     * @return \Cosman\Queue\Store\Model\Output|NULL
     */
    public function fetchById(int $id, Job $job = null, Client $client = null, Project $project = null): ?Output;

    /**
     * Fetches a single output by code
     *
     * If job is provided, only a matching output belonging to job will returned
     *
     * @param string $code
     * @param Job $job
     * @param Client $client
     * @param Project $project
     * @return \Cosman\Queue\Store\Model\Output|NULL
     */
    public function fetchByCode(string $code, Job $job = null, Client $client = null, Project $project = null): ?Output;

    /**
     * Creates a single output
     *
     * @param Output $output
     * @return int Unique Id of newly created output
     */
    public function create(Output $output): int;

    /**
     * Creates at least one output
     *
     * @param Output ...$outputs
     * @return bool
     */
    public function createMany(Output ...$outputs): bool;

    /**
     * Updates a collection of outputs
     *
     * @param Job ...$jobs
     * @return int Number of jobs updated
     */
    public function update(Output ...$outputs): int;

    /**
     * Deletes a collection of outputs
     *
     * @param Output ...$outputs
     * @return int Number of jobs deleted
     */
    public function delete(Output ...$outputs): int;
}