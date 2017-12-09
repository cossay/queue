<?php
declare(strict_types = 1);
namespace Cosman\Queue\ServiceProvider;

use Cosman\Queue\Http\Auth\AccessManager;
use Cosman\Queue\Http\Controller\ClientController;
use Cosman\Queue\Http\Controller\JobController;
use Cosman\Queue\Http\Controller\OutputController;
use Cosman\Queue\Http\Controller\ProjectController;
use Cosman\Queue\Http\Converter\JobConverter;
use Cosman\Queue\Http\Converter\OutputConverter;
use Cosman\Queue\Http\Converter\ProjectConverter;
use Cosman\Queue\Http\Response\Response;
use Cosman\Queue\Store\Repository\ClientRepository;
use Cosman\Queue\Store\Repository\JobRepository;
use Cosman\Queue\Store\Repository\OutputRepository;
use Cosman\Queue\Store\Repository\ProjectRepository;
use Cosman\Queue\Store\Validation\ClientValidator;
use Cosman\Queue\Store\Validation\JobValidator;
use Cosman\Queue\Store\Validation\OutputValidator;
use Cosman\Queue\Store\Validation\ProjectValidator;
use Cosman\Queue\Store\Validation\Constraint\ClientUniqueEmailValidator;
use Illuminate\Database\Connection;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;

/**
 * Silex integration service provider
 *
 * @author cosman
 *        
 */
class SilexServiceProvider implements ServiceProviderInterface, ControllerProviderInterface
{

    /**
     *
     * {@inheritdoc}
     * @see \Pimple\ServiceProviderInterface::register()
     */
    public function register(Container $container)
    {
        // DATABASE CONNECTION
        $container['cosman.queue.database.connection.default'] = function (Container $container) {
            
            $connection = $container['cosman.queue.database.connection'] ?? null;
            
            if (! ($connection instanceof Connection)) {
                $connection = new Connection(new \PDO('mysql:host=localhost;dbname=queues', 'root', ''));
            }
            
            return $connection;
        };
        
        // REPOSITORIES
        $container['cosman.queue.repo.client'] = function (Container $container) {
            return new ClientRepository($container['cosman.queue.database.connection.default']);
        };
        
        $container['cosman.queue.repo.project'] = function (Container $container) {
            return new ProjectRepository($container['cosman.queue.database.connection.default']);
        };
        
        $container['cosman.queue.repo.job'] = function (Container $container) {
            return new JobRepository($container['cosman.queue.database.connection.default']);
        };
        
        $container['cosman.queue.repo.output'] = function (Container $container) {
            return new OutputRepository($container['cosman.queue.database.connection.default']);
        };
        
        // VALIDATION CONSTRAINTS'
        $container['cosman.queue.validator.constraint.client.email.unique'] = function (Container $container) {
            $constraint = new ClientUniqueEmailValidator();
            $constraint->setRepository($container['cosman.queue.repo.client']);
            
            return $constraint;
        };
        
        $container['validator.validator_service_ids'] = function () {
            return array(
                'cosman.queue.validator.constraint.client.email.unique' => 'cosman.queue.validator.constraint.client.email.unique'
            );
        };
        
        // VALIDATORS
        $container['cosman.queue.validator.client'] = function (Container $container) {
            return new ClientValidator($container['validator']);
        };
        
        $container['cosman.queue.validator.project'] = function (Container $container) {
            return new ProjectValidator($container['validator']);
        };
        
        $container['cosman.queue.validator.job'] = function (Container $container) {
            return new JobValidator($container['validator']);
        };
        
        $container['cosman.queue.validator.output'] = function (Container $container) {
            return new OutputValidator($container['validator']);
        };
        
        // CONVERTERS
        $container['cosman.queue.converter.project'] = function (Container $container) {
            return new ProjectConverter($container['cosman.queue.repo.project']);
        };
        
        $container['cosman.queue.converter.job'] = function (Container $container) {
            return new JobConverter($container['cosman.queue.repo.job']);
        };
        
        $container['cosman.queue.converter.output'] = function (Container $container) {
            return new OutputConverter($container['cosman.queue.repo.output']);
        };
        
        // CONTROLLERS
        $container['cosman.queue.validator.client'] = function (Container $container) {
            return new ClientValidator($container['validator']);
        };
        
        $container['cosman.queue.validator.project'] = function (Container $container) {
            return new ProjectValidator($container['validator']);
        };
        
        $container['cosman.queue.validator.job'] = function (Container $container) {
            return new JobValidator($container['validator']);
        };
        
        $container['cosman.queue.validator.output'] = function (Container $container) {
            return new OutputValidator($container['validator']);
        };
        
        // RESPONSE
        $container['cosman.queue.response'] = function (Container $container) {
            return new Response();
        };
        
        // ACCESS CONTROLL
        $container['cosman.queue.auth.accessmanager'] = function (Container $container) {
            return new AccessManager($container['cosman.queue.repo.client']);
        };
        
        // CONTROLLERS
        $container['cosman.queue.controller.client'] = function (Container $container) {
            return new ClientController($container['request_stack']->getCurrentRequest(), $container['cosman.queue.response'], $container['cosman.queue.auth.accessmanager'], $container['cosman.queue.repo.client'], $container['cosman.queue.validator.client']);
        };
        
        $container['cosman.queue.controller.project'] = function (Container $container) {
            return new ProjectController($container['request_stack']->getCurrentRequest(), $container['cosman.queue.response'], $container['cosman.queue.auth.accessmanager'], $container['cosman.queue.repo.project'], $container['cosman.queue.validator.project']);
        };
        
        $container['cosman.queue.controller.job'] = function (Container $container) {
            return new JobController($container['request_stack']->getCurrentRequest(), $container['cosman.queue.response'], $container['cosman.queue.auth.accessmanager'], $container['cosman.queue.repo.job'], $container['cosman.queue.validator.job']);
        };
        
        $container['cosman.queue.controller.output'] = function (Container $container) {
            return new OutputController($container['request_stack']->getCurrentRequest(), $container['cosman.queue.response'], $container['cosman.queue.auth.accessmanager'], $container['cosman.queue.repo.output'], $container['cosman.queue.validator.output']);
        };
    }

    /**
     *
     * {@inheritdoc}
     * @see \Silex\Api\ControllerProviderInterface::connect()
     */
    public function connect(Application $app)
    {
        $mountPoint = $app['controllers_factory'];
        
        $mountPoint->post('clients', 'cosman.queue.controller.client:postClients');
        
        $mountPoint->post('projects', 'cosman.queue.controller.project:postProjects');
        $mountPoint->get('projects', 'cosman.queue.controller.project:getProjects');
        $mountPoint->get('projects/{project}', 'cosman.queue.controller.project:getProject')->convert('project', 'cosman.queue.converter.project:code');
        $mountPoint->put('projects/{project}', 'cosman.queue.controller.project:putProjects')->convert('project', 'cosman.queue.converter.project:code');
        $mountPoint->delete('projects/{project}', 'cosman.queue.controller.project:deleteProjects')->convert('project', 'cosman.queue.converter.project:code');
        
        $mountPoint->post('projects/{project}/jobs', 'cosman.queue.controller.job:postJobs')->convert('project', 'cosman.queue.converter.project:code');
        $mountPoint->get('projects/{project}/jobs', 'cosman.queue.controller.job:getJobs')->convert('project', 'cosman.queue.converter.project:code');
        $mountPoint->get('projects/{project}/jobs/{job}', 'cosman.queue.controller.job:getJob')
            ->convert('project', 'cosman.queue.converter.project:code')
            ->convert('job', 'cosman.queue.converter.job:code');
        $mountPoint->get('projects/{project}/jobs/{job}/outputs', 'cosman.queue.controller.output:getOutputs')
            ->convert('project', 'cosman.queue.converter.project:code')
            ->convert('job', 'cosman.queue.converter.job:code');
        $mountPoint->get('projects/{project}/jobs/{job}/outputs/{output}', 'cosman.queue.controller.output:getOutputs')
            ->convert('project', 'cosman.queue.converter.project:code')
            ->convert('job', 'cosman.queue.converter.job:code')
            ->convert('output', 'cosman.queue.converter.output:code');
        
        return $mountPoint;
    }
}
