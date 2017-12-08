<?php
declare(strict_types = 1);
namespace Cosman\Queue\Http\Auth;

use Cosman\Queue\Http\Exception\AuthorizationFailureException;
use Cosman\Queue\Store\Model\Client;
use Symfony\Component\HttpFoundation\Request;
use Cosman\Queue\Store\Repository\ClientRepositoryInterface;

/**
 * Access controller manager
 * 
 * @author cosman
 *        
 */
class AccessManager implements AccessManagerInterface
{

    /**
     *
     * @var ClientRepositoryInterface
     */
    protected $repository;

    /**
     *
     * @param ClientRepositoryInterface $repository
     */
    public function __construct(ClientRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     *
     * {@inheritdoc}
     * @see \Cosman\Queue\Http\Auth\AccessManagerInterface::getClient()
     */
    public function getClient(Request $request): Client
    {
        $token = $request->headers->get(static::AUTHORIZATION_HEADER_NAME);
        
        if (empty($token)) {
            throw new AuthorizationFailureException('Missing authorization token.');
        }
        
        $client = $this->repository->fetchByToken($token);
        
        if (null === $client) {
            throw new AuthorizationFailureException('Invalid authorization token.');
        }
        
        if ($client->isBlocked()) {
            throw new AuthorizationFailureException();
        }
        
        return $client;
    }
}