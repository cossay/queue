<?php
declare(strict_types = 1);
namespace Cosman\Queue;

use Silex\Application;
use Illuminate\Database\Capsule\Manager;
use Cosman\Queue\Http\Response\Response;
use Cosman\Queue\ServiceProvider\SilexServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
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
     * @param Manager $capsule
     * @param array $values
     */
    public function __construct(Manager $capsule, bool $debug = false, array $values = [])
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
        
        $capsule->setAsGlobal();
        
        $this->register(new ValidatorServiceProvider());
        $this->register(new SilexServiceProvider(), array(
            'cosman.queue.database.connection' => $capsule
        ));
        $this->register(new ServiceControllerServiceProvider());
        $this->mount('v1', new SilexServiceProvider());
    }
}