<?php
declare(strict_types = 1);
namespace Cosman\Queue\Http\Exception;

use Cosman\Queue\Http\Response\Response;

/**
 *
 * @author cosman
 *        
 */
class AuthorizationFailureException extends QueueException
{

    public function __construct($message = 'Access denied', $code = Response::HTTP_UNAUTHORIZED, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}