<?php
declare(strict_types = 1);
namespace Cosman\Queue\Http\Converter;

use Cosman\Queue\Store\Repository\JobRepositoryInterface;
use Cosman\Queue\Store\Model\Job;
use Cosman\Queue\Http\Exception\QueueException;
use Cosman\Queue\Http\Response\Response;

/**
 * Job converter
 *
 * @author cosman
 *        
 */
class JobConverter
{

    /**
     *
     * @var JobRepositoryInterface
     */
    protected $repository;

    /**
     *
     * @param JobRepositoryInterface $repository
     */
    public function __construct(JobRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Converts a given id into a job instance
     *
     * @param int $id
     * @throws QueueException
     * @return Job
     */
    public function id(int $id): Job
    {
        $job = $this->repository->fetchById($id);
        
        if (! ($job instanceof Job)) {
            throw new QueueException('Requested job not found', Response::HTTP_NOT_FOUND);
        }
        
        return $job;
    }

    /**
     * Converts a given code into a job instance
     *
     * @param string $code
     * @throws QueueException
     * @return Job
     */
    public function code(string $code): Job
    {
        $job = $this->repository->fetchByCode($code);
        
        if (! ($job instanceof Job)) {
            throw new QueueException('Requested job not found', Response::HTTP_NOT_FOUND);
        }
        
        return $job;
    }
}