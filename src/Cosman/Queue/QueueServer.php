<?php
declare(strict_types = 1);
namespace Cosman\Queue;

use Cosman\Queue\Http\Response\Response;
use Cosman\Queue\ServiceProvider\SilexServiceProvider;
use Illuminate\Database\Connection;
use Silex\Application;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as SymphonyResponse;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Exception;

/**
 *
 * @author cosman
 *        
 */
class QueueServer extends Application
{

    /**
     * 
     * @param Connection $connection
     * @param bool $debug
     * @param array $values
     */
    public function __construct(Connection $connection, bool $debug = false, array $values = [])
    {
        parent::__construct($values);
        
        $this['debug'] = $debug;
        
        $this->error(function (Exception $e) {
            $code = (int) $e->getCode();
            
            if (! $code) {
                if ($e instanceof MethodNotAllowedHttpException) {
                    $code = 405;
                }
            }
            
            return (new Response())->exception($e, $code);
        });
        
        $this->after(function (Request $request, SymphonyResponse $response) {
            
            if ($response instanceof Response) {
                return $response->setStatusCode(200);
            }
            
            return $response;
        });
        
        $this->register(new ValidatorServiceProvider());
        $this->register(new SilexServiceProvider(), array(
            'cosman.queue.database.connection' => $connection
        ));
        $this->register(new ServiceControllerServiceProvider());
        $this->mount('v1', new SilexServiceProvider());
    }
}