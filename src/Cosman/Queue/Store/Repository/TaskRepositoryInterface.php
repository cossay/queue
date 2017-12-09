<?php
declare(strict_types = 1);
namespace Cosman\Queue\Store\Repository;

use Cosman\Queue\Store\Model\Output;
use Cosman\Queue\Store\Model\Job;

/**
 *
 * @author cosman
 *        
 */
interface TaskRepositoryInterface extends RepositoryInterface
{

    /**
     * Reads a number of waiting jobs
     *
     * @param int $limit
     * @param int $offset
     * @return \Cosman\Queue\Store\Model\Job[]
     */
    public function read(int $limit = 1000, int $offset = 0): iterable;

    /**
     * Updates a collection of jobs
     *
     * @param Job ...$jobs
     * @return int
     */
    public function update(Job ...$jobs): int;

    /**
     * Releases a collection of jobs so other processes can process them if neccessary
     *
     * @param Job ...$jobs
     * @return int
     */
    public function release(Job ...$jobs): int;

    /**
     * Writes a number of outputs
     *
     * @param Output ...$outputs
     * @return bool
     */
    public function writeOutputs(Output ...$outputs): bool;
}