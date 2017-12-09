<?php
namespace Cosman\Queue\Http\Controller;

use Cosman\Queue\Http\Response\Response;
use Cosman\Queue\Store\Validation\ClientValidator;
use Symfony\Component\HttpFoundation\Request;
use Exception;
use Cosman\Queue\Store\Repository\ClientRepositoryInterface;
use Cosman\Queue\Store\Model\Client;
use Cosman\Queue\Http\Auth\AccessManagerInterface;

/**
 * Client controller class
 *
 * @author cosman
 *        
 */
class ClientController extends Controller
{

    /**
     *
     * @var ClientRepositoryInterface
     */
    protected $repository;

    /**
     *
     * @var ClientValidator
     */
    protected $validator;

    /**
     *
     * @param Request $request
     * @param Response $response
     * @param ClientValidator $validator
     */
    public function __construct(Request $request, Response $response, AccessManagerInterface $accessManager, ClientRepositoryInterface $repository, ClientValidator $validator)
    {
        parent::__construct($request, $response, $accessManager);
        
        $this->repository = $repository;
        
        $this->validator = $validator;
    }

    /**
     * Responses to request to create client
     *
     * @return Response
     */
    public function postClients(): Response
    {
        try {
            
            $clientDetails = array(
                'name' => $this->request->request->get('name'),
                'email' => $this->request->request->get('email')
                // 'password' => $this->request->request->get('password')
            );
            
            if (! $this->validator->validate($clientDetails)) {
                return $this->response->error($this->validator->getErrors(), Response::HTTP_UNPROCESSABLE_ENTITY, $this->validator->getFirstError());
            }
            
            $code = strtoupper(sha1(sprintf('%s-%s', microtime(), $clientDetails['email'])));
            
            $client = new Client();
            $client->setToken($code);
            $client->setName($clientDetails['name']);
            $client->setEmail($clientDetails['email']);
            // $client->setPassword($clientDetails['password']);
            
            $clientId = $this->repository->create($client);
            
            $createdClient = $this->repository->fetchById($clientId);
            
            if ($createdClient instanceof Client) {
                $createdClient = $createdClient->toArray();
            }
            
            return $this->response->respond($createdClient);
        } catch (Exception $e) {
            return $this->response->exception($e);
        }
    }
}
