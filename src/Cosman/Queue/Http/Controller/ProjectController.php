<?php
declare(strict_types = 1);
namespace Cosman\Queue\Http\Controller;

use Cosman\Queue\Http\Auth\AccessManagerInterface;
use Cosman\Queue\Http\Response\Response;
use Cosman\Queue\Store\Model\Project;
use Cosman\Queue\Store\Repository\ProjectRepositoryInterface;
use Cosman\Queue\Store\Validation\ProjectValidator;
use Symfony\Component\HttpFoundation\Request;
use Exception;

/**
 *
 * @author cosman
 *        
 */
class ProjectController extends Controller
{

    /**
     *
     * @var ProjectRepositoryInterface
     */
    protected $repository;

    /**
     *
     * @var ProjectValidator
     */
    protected $validator;

    /**
     *
     * @param Request $request
     * @param Response $response
     * @param AccessManagerInterface $accessManager
     * @param ProjectRepositoryInterface $repository
     * @param ProjectValidator $validator
     */
    public function __construct(Request $request, Response $response, AccessManagerInterface $accessManager, ProjectRepositoryInterface $repository, ProjectValidator $validator)
    {
        parent::__construct($request, $response, $accessManager);
        
        $this->repository = $repository;
        
        $this->validator = $validator;
    }

    /**
     * Responses to request to create a single project for current client
     *
     * @return \Cosman\Queue\Http\Response\Response
     */
    public function postProjects(): Response
    {
        try {
            
            $client = $this->accessManager->getClient($this->request);
            
            $attributes = array(
                'name' => $this->request->request->get('name'),
                'description' => $this->request->request->get('description')
            );
            
            if (! $this->validator->validate($attributes)) {
                return $this->response->error($this->validator->getErrors(), Response::HTTP_UNPROCESSABLE_ENTITY, $this->validator->getFirstError());
            }
            
            $project = new Project();
            $project->setClient($client);
            $project->setName($attributes['name']);
            $project->setDescription($attributes['description']);
            
            $projectId = $this->repository->create($project);
            
            return $this->response->respond($this->repository->fetchById($projectId, $client));
        } catch (Exception $e) {
            return $this->response->exception($e);
        }
    }

    /**
     * Responses to request to update a single project for current client
     *
     * @param Project $project
     * @return \Cosman\Queue\Http\Response\Response
     */
    public function putProject(Project $project): Response
    {
        try {
            
            $client = $this->accessManager->getClient($this->request);
            
            if (!$client->ownsProject($project)) {
                return $this->response->error(null, Response::HTTP_FORBIDDEN, static::MESSAGE_ACCESS_FORBIDDEN);
            }
            
            //Use existing project attributes as default so that clients provide only properties they wish to update
            $attributes = array(
                'name' => $this->request->request->get('name', $project->getName()),
                'description' => $this->request->request->get('description', $project->getDescription())
            );
            
            if (! $this->validator->validate($attributes)) {
                return $this->response->error($this->validator->getErrors(), Response::HTTP_UNPROCESSABLE_ENTITY, $this->validator->getFirstError());
            }
            
            $copiedProject = clone $project;
            $copiedProject->setName($attributes['name']);
            $copiedProject->setDescription($attributes['description']);
            
            $this->repository->update($copiedProject);
            
            return $this->response->respond($this->repository->fetchById($project->getId(), $client));
        } catch (Exception $e) {
            return $this->response->exception($e);
        }
    }

    /**
     * Responses to request to fetch a number of projects for current client
     *
     * @return \Cosman\Queue\Http\Response\Response
     */
    public function getProjects(): Response
    {
        try {
            $client = $this->accessManager->getClient($this->request);
            
            $this->verifyPaginationParameters();
            
            $counts = $this->repository->count($client);
            
            if (0 === $counts) {
                return $this->response->collection();
            }
            
            $projects = $this->repository->fetch($this->limit, $this->offset, $client);
            
            return $this->response->collection($projects, $counts, $this->offset);
        } catch (Exception $e) {
            return $this->response->exception($e);
        }
    }

    /**
     * Responses to request to fetch a single project for current client
     *
     * @param Project $project
     * @return \Cosman\Queue\Http\Response\Response
     */
    public function getProject(Project $project): Response
    {
        try {
            $client = $this->accessManager->getClient($this->request);
            
            if (!$client->ownsProject($project)) {
                return $this->response->error(null, Response::HTTP_FORBIDDEN, static::MESSAGE_ACCESS_FORBIDDEN);
            }
            
            return $this->response->respond($project);
        } catch (Exception $e) {
            return $this->response->exception($e);
        }
    }

    /**
     * Responses to request to delete a single project for current client
     *
     * @param Project $project
     * @return \Cosman\Queue\Http\Response\Response
     */
    public function deleteProjects(Project $project): Response
    {
        try {
            $client = $this->accessManager->getClient($this->request);
            
            if (!$client->ownsProject($project)) {
                return $this->response->error(null, Response::HTTP_FORBIDDEN, static::MESSAGE_ACCESS_FORBIDDEN);
            }
            
            $this->repository->delete($project);
            
            return $this->response->respond();
        } catch (Exception $e) {
            return $this->response->exception($e);
        }
    }
}