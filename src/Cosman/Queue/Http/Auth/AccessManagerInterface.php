<?php
declare(strict_types = 1);
namespace Cosman\Queue\Http\Auth;

use Cosman\Queue\Store\Model\Client;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 * @author cosman
 *        
 */
interface AccessManagerInterface
{

    const AUTHORIZATION_HEADER_NAME = 'QUEUE-ACCESS-TOKEN';

    /**
     * Returns client who is currently access the system
     * 
     * @param Request $request
     * @return \Cosman\Queue\Store\Model\Client
     */
    public function getClient(Request $request): Client;
}