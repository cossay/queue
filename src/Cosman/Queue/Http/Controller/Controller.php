<?php
declare(strict_types = 1);
namespace Cosman\Queue\Http\Controller;

use Cosman\Queue\Http\Auth\AccessManagerInterface;
use Cosman\Queue\Http\Exception\QueueException;
use Cosman\Queue\Http\Response\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * Base controller class
 *
 * @author cosman
 *        
 */
class Controller
{

    const MESSAGE_ACCESS_FORBIDDEN = 'Yo do not have the required permission to access requested resource.';

    const MESSAGE_NOT_FOUND = 'Requested resource not found.';

    const MESSAGE_INVALID_JSON_INPUT = 'Invalid JSON data received.';

    /**
     *
     * @var Request
     */
    protected $request;

    /**
     *
     * @var Response
     */
    protected $response;

    /**
     *
     * @var AccessManagerInterface
     */
    protected $accessManager;

    /**
     *
     * @var int
     */
    protected $limit = 50;

    /**
     *
     * @var int
     */
    protected $offset = 0;

    /**
     *
     * @param Request $request
     * @param Response $response
     * @param AccessManagerInterface $accessManager
     */
    public function __construct(Request $request, Response $response, AccessManagerInterface $accessManager)
    {
        $this->request = $request;
        
        $this->response = $response;
        
        $this->accessManager = $accessManager;
        
        $targetRequests = [Request::METHOD_POST, Request::METHOD_PUT];
        
        if (in_array($this->request->getMethod(), $targetRequests)) {
            
            $jsonContentType = 'application/json';
            
            $contentType = $this->request->headers->get('content-type', $jsonContentType);
            
            if (0 === strpos($contentType, $jsonContentType)) {
                
                $rawContent = $this->request->getContent();
                
                if ($rawContent && is_string($rawContent)) {
                    
                    $decodedContent = json_decode($rawContent, true);
                    
                    if (JSON_ERROR_NONE !== json_last_error()) {
                        
                        throw new QueueException(static::MESSAGE_INVALID_JSON_INPUT, Response::HTTP_BAD_REQUEST);
                    }
                    
                    $this->request->request->replace(is_array($decodedContent) ? $decodedContent : []);
                }
            }
        }
    }

    /**
     * Checks validity of pagination parameters
     *
     * @throws QueueException
     */
    protected function verifyPaginationParameters(): void
    {
        $this->limit = $this->request->query->getInt('limit', $this->limit);
        
        $this->offset = $this->request->query->getInt('offset', $this->offset);
        
        if (0 >= $this->limit) {
            throw new QueueException('"limit" must be an integer greater than zero (0).');
        }
        
        if (0 > $this->offset) {
            throw new QueueException('"offset" must be an integer greater than or equal to zero (0).');
        }
    }
}
