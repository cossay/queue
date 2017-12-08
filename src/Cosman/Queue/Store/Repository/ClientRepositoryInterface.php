<?php
declare(strict_types = 1);
namespace Cosman\Queue\Store\Repository;

use Cosman\Queue\Store\Model\Client;

/**
 * Client repository interface
 *
 * @author cosman
 *        
 */
interface ClientRepositoryInterface
{

    /**
     * Returns number of available client
     *
     * @return int
     */
    public function count(): int;

    /**
     * Fetches a number of clients
     *
     * @param int $limit
     * @param int $offset
     * @return \Cosman\Queue\Store\Model\Client[]
     */
    public function fetch(int $limit, int $offset): iterable;

    /**
     * Fetches a single clien by id
     *
     * @param int $id
     * @return \Cosman\Queue\Store\Model\Client|NULL
     */
    public function fetchById(int $id): ?Client;

    /**
     * Fetches a single client by token
     *
     * @param string $token
     * @return \Cosman\Queue\Store\Model\Client|NULL
     */
    public function fetchByToken(string $token): ?Client;
    
    /**
     * Fetches a single client by unique email address
     *
     * @param string $email
     * @return \Cosman\Queue\Store\Model\Client|NULL
     */
    public function fetchByEmail(string $email): ?Client;

    /**
     * Creates a single client
     *
     * @param Client $client
     * @return int Unique Id of newly created client
     */
    public function create(Client $client): int;

    /**
     * Updates a collection of clients
     *
     * @param Client ...$clients
     * @return int Number of affected clients
     */
    public function update(Client ...$clients): int;

    /**
     * Deletes a number of clients
     *
     * @param Client ...$clients
     * @return int Number of affected clients
     */
    public function delete(Client ...$clients): int;
}