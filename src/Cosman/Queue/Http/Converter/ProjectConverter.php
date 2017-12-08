<?php
declare(strict_types = 1);
namespace Cosman\Queue\Http\Converter;

use Cosman\Queue\Store\Repository\ProjectRepositoryInterface;
use Cosman\Queue\Store\Model\Project;
use Cosman\Queue\Http\Exception\QueueException;
use Cosman\Queue\Http\Response\Response;

/**
 * Project converter
 * 
 * @author cosman
 *        
 */
class ProjectConverter
{

    /**
     *
     * @var ProjectRepositoryInterface
     */
    protected $repository;

    /**
     *
     * @param ProjectRepositoryInterface $repository
     */
    public function __construct(ProjectRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Converts a given Id into a project instance
     *
     * @param int $id
     * @throws QueueException
     * @return Project
     */
    public function id(int $id): Project
    {
        $project = $this->repository->fetchById($id);
        
        if (! ($project instanceof Project)) {
            throw new QueueException('Requested project not found.', Response::HTTP_NOT_FOUND);
        }
        
        return $project;
    }

    /**
     * Converts a given code into a project instance
     *
     * @param string $code
     * @throws QueueException
     * @return Project
     */
    public function code(string $code): Project
    {
        $project = $this->repository->fetchByCode($code);
        
        if (! ($project instanceof Project)) {
            throw new QueueException('Requested project not found.', Response::HTTP_NOT_FOUND);
        }
        
        return $project;
    }
}