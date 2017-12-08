<?php
declare(strict_types = 1);
namespace Cosman\Queue\Http\Converter;

use Cosman\Queue\Http\Exception\QueueException;
use Cosman\Queue\Http\Response\Response;
use Cosman\Queue\Store\Model\Output;
use Cosman\Queue\Store\Repository\OutputRepositoryInterface;

/**
 * Job converter
 *
 * @author cosman
 *        
 */
class OutputConverter
{

    /**
     *
     * @var OutputRepositoryInterface
     */
    protected $repository;

    /**
     *
     * @param OutputRepositoryInterface $repository
     */
    public function __construct(OutputRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Converts a given id into a output instance
     *
     * @param int $id
     * @throws QueueException
     * @return \Cosman\Queue\Store\Model\Output
     */
    public function id(int $id): Output
    {
        $output = $this->repository->fetchById($id);
        
        if (! ($output instanceof Output)) {
            throw new QueueException('Requested output not found', Response::HTTP_NOT_FOUND);
        }
        
        return $output;
    }

    /**
     * Converts a given code into a output instance
     *
     * @param string $code
     * @throws QueueException
     * @return \Cosman\Queue\Store\Model\Output
     */
    public function code(string $code): Output
    {
        $ouput = $this->repository->fetchByCode($code);
        
        if (! ($ouput instanceof Output)) {
            throw new QueueException('Requested output not found', Response::HTTP_NOT_FOUND);
        }
        
        return $ouput;
    }
}