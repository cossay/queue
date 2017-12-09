<?php
declare(strict_types = 1);
namespace Cosman\Queue\Store\Repository;

use Cosman\Queue\Store\Model\BaseModel;
use Cosman\Queue\Store\Model\Client;
use Cosman\Queue\Store\Model\Project;
use Cosman\Queue\Store\Table\ClientTable;
use Cosman\Queue\Store\Table\ProjectTable;
use Cosman\Queue\Support\DateTime\DateTime;
use Illuminate\Database\Query\Builder;
use Exception;

/**
 *
 * @author cosman
 *        
 */
class ProjectRepository extends BaseRepository implements ProjectRepositoryInterface
{

    const RELATION_CLIENT = 'project_client_tb';

    /**
     *
     * @param array $selectableFields
     * @param Client $client
     * @return Builder
     */
    protected function withJoins(array $selectableFields = [], Client $client = null): Builder
    {
        $query = $this->connection->table(ProjectTable::NAME)->leftJoin(ClientTable::NAME, ClientTable::FIELD_ID, '=', ProjectTable::FIELD_CLIENT_ID);
        
        if (empty($selectableFields)) {
            $selectableFields = $this->createSelectableFieldList();
        }
        
        if (null !== $client) {
            $query->where(ProjectTable::FIELD_CLIENT_ID, '=', $client->getId());
        }
        
        return $query->select($selectableFields);
    }

    /**
     *
     * {@inheritdoc}
     * @see \Cosman\Queue\Store\Repository\ProjectRepositoryInterface::count()
     */
    public function count(Client $client = null): int
    {
        $query = $this->withJoins([], $client);
        
        return $query->count();
    }

    /**
     *
     * {@inheritdoc}
     * @see \Cosman\Queue\Store\Repository\ProjectRepositoryInterface::fetch()
     */
    public function fetch(int $limit, int $offset, Client $client = null): array
    {
        $query = $this->withJoins([], $client)
            ->limit($limit)
            ->offset($offset);
        
        return $this->formatCollection($query->get());
    }

    /**
     *
     * {@inheritdoc}
     * @see \Cosman\Queue\Store\Repository\ProjectRepositoryInterface::fetchById()
     */
    public function fetchById(int $id, Client $client = null): ?Project
    {
        $query = $this->withJoins([], $client)->where(ProjectTable::FIELD_ID, '=', $id);
        
        return $this->format($query->first());
    }

    /**
     *
     * {@inheritdoc}
     * @see \Cosman\Queue\Store\Repository\ProjectRepositoryInterface::fetchByCode()
     */
    public function fetchByCode(string $code, Client $client = null): ?Project
    {
        $query = $this->withJoins([], $client)->where(ProjectTable::FIELD_CODE, '=', $code);
        
        return $this->format($query->first());
    }

    /**
     *
     * {@inheritdoc}
     * @see \Cosman\Queue\Store\Repository\ProjectRepositoryInterface::create()
     */
    public function create(Project $project): int
    {
        $code = strtoupper(sha1(sprintf('%s-%d', microtime(), $project->getClient()->getId())));
        
        $attributes = array(
            ProjectTable::FIELD_CLIENT_ID => $project->getClient()->getId(),
            ProjectTable::FIELD_CODE => $code,
            ProjectTable::FIELD_NAME => $project->getName(),
            ProjectTable::FIELD_DESCRIPTION => $project->getDescription(),
            ProjectTable::FIELD_CREATED_AT => new DateTime(),
            ProjectTable::FIELD_UPDATED_AT => null
        );
        
        return $this->connection->table(ProjectTable::NAME)->insertGetId($attributes);
    }

    /**
     *
     * {@inheritdoc}
     * @see \Cosman\Queue\Store\Repository\ProjectRepositoryInterface::update()
     */
    public function update(Project ...$projects): int
    {
        $affetedRows = 0;
        
        try {
            
            $this->connection->beginTransaction();
            
            foreach ($projects as $project) {
                if ($project->getId()) {
                    $attributes = array(
                        ProjectTable::FIELD_CLIENT_ID => $project->getClient()->getId(),
                        ProjectTable::FIELD_NAME => $project->getName(),
                        ProjectTable::FIELD_DESCRIPTION => $project->getDescription(),
                        ProjectTable::FIELD_UPDATED_AT => new DateTime()
                    );
                    
                    $affetedRows += $this->connection->table(ProjectTable::NAME)
                        ->where(ProjectTable::FIELD_ID, '=', $project->getId())
                        ->update($attributes);
                }
            }
            
            $this->connection->commit();
        } catch (Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }
        
        return $affetedRows;
    }

    /**
     *
     * {@inheritdoc}
     * @see \Cosman\Queue\Store\Repository\ProjectRepositoryInterface::delete()
     */
    public function delete(Project ...$projects): int
    {
        $ids = [];
        
        foreach ($projects as $project) {
            if ($project->getId()) {
                $ids[] = $project->getId();
            }
        }
        
        return $this->connection->table(ProjectTable::NAME)
            ->whereIn(ProjectTable::FIELD_ID, $ids)
            ->delete();
    }

    /**
     *
     * @param mixed $model
     * @param array $relations
     * @return BaseModel|NULL
     */
    protected function format($model, array $relations = []): ?BaseModel
    {
        $project = Project::createInstance($model);
        
        if (! ($project instanceof Project)) {
            return null;
        }
        
        $client = Client::createInstance($model, static::RELATION_CLIENT);
        
        if ($client instanceof Client) {
            $project->setClient($client);
        }
        
        return $project;
    }

    /**
     *
     * @return array
     */
    protected function getSelectableFields(): array
    {
        $fields = array(
            ProjectTable::definition()->getFields()->all(),
            ClientTable::definition()->aliasFields(static::RELATION_CLIENT)->all()
        );
        
        return array_merge(...$fields);
    }
}