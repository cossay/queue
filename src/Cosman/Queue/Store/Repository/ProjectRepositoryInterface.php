<?php
declare(strict_types = 1);
namespace Cosman\Queue\Store\Repository;

use Cosman\Queue\Store\Model\Client;
use Cosman\Queue\Store\Model\Project;

/**
 *
 * @author cosman
 *        
 */
interface ProjectRepositoryInterface
{

    /**
     * Returns number of projects
     *
     * if a client is provider, only projects belong to the client will be counted
     *
     * @param Client $client
     * @return int
     */
    public function count(Client $client = null): int;

    /**
     * Fetches a number of projects
     *
     * @param int $limit
     * @param int $offset
     * @param Client $client
     * @return \Cosman\Queue\Store\Model\Project[]
     */
    public function fetch(int $limit, int $offset, Client $client = null): array;

    /**
     * Fetches a single project by id
     *
     * @param int $id
     * @param Client $client
     * @return \Cosman\Queue\Store\Model\Project|NULL
     */
    public function fetchById(int $id, Client $client = null): ?Project;

    /**
     * Fetches a single project by unique code
     *
     * @param string $code
     * @param Client $client
     * @return \Cosman\Queue\Store\Model\Project|NULL
     */
    public function fetchByCode(string $code, Client $client = null): ?Project;

    /**
     * Creates a single project
     *
     * @param Project $project
     * @return int
     */
    public function create(Project $project): int;

    /**
     * Update a list of projects
     *
     * @param Project ...$projects
     * @return int
     */
    public function update(Project ...$projects): int;

    /**
     * Deletes a list of projects
     *
     * @param Project ...$projects
     * @return int
     */
    public function delete(Project ...$projects): int;
}