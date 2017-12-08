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
     * @param string[] $selectableFields
     * @return \Illuminate\Database\Query\Builder
     */
    protected function withJoins(array $selectableFields = []): Builder
    {
        $query = $this->capsule->table(ProjectTable::NAME)->leftJoin(ClientTable::NAME, ClientTable::FIELD_ID, '=', ProjectTable::FIELD_CLIENT_ID);
        
        if (empty($selectableFields)) {
            $selectableFields = $this->createSelectableFieldList();
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
        $query = $this->withJoins();
        
        if (null !== $client) {
            $query->where(ProjectTable::FIELD_CLIENT_ID, '=', $client->getId());
        }
        
        return $query->count();
    }

    /**
     *
     * {@inheritdoc}
     * @see \Cosman\Queue\Store\Repository\ProjectRepositoryInterface::fetch()
     */
    public function fetch(int $limit, int $offset, Client $client = null): array
    {
        $query = $this->withJoins()
            ->limit($limit)
            ->offset($offset);
        
        if (null !== $client) {
            $query->where(ProjectTable::FIELD_CLIENT_ID, '=', $client->getId());
        }
        
        return $this->formatCollection($query->get());
    }

    /**
     *
     * {@inheritdoc}
     * @see \Cosman\Queue\Store\Repository\ProjectRepositoryInterface::fetchById()
     */
    public function fetchById(int $id, Client $client = null): ?Project
    {
        $query = $this->withJoins()->where(ProjectTable::FIELD_ID, '=', $id);
        
        if (null !== $client) {
            $query->where(ProjectTable::FIELD_CLIENT_ID, '=', $client->getId());
        }
        
        return $this->format($query->first());
    }

    /**
     *
     * {@inheritdoc}
     * @see \Cosman\Queue\Store\Repository\ProjectRepositoryInterface::fetchByCode()
     */
    public function fetchByCode(string $code, Client $client = null): ?Project
    {
        $query = $this->withJoins()->where(ProjectTable::FIELD_CODE, '=', $code);
        
        if (null !== $client) {
            $query->where(ProjectTable::FIELD_CLIENT_ID, '=', $client->getId());
        }
        
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
        
        return $this->capsule->table(ProjectTable::NAME)->insertGetId($attributes);
    }

    /**
     *
     * {@inheritdoc}
     * @see \Cosman\Queue\Store\Repository\ProjectRepositoryInterface::update()
     */
    public function update(Project ...$projects): int
    {
        $connection = $this->capsule->connection();
        
        try {
            $affetedRows = 0;
            
            $connection->beginTransaction();
            
            foreach ($projects as $project) {
                if ($project->getId()) {
                    $attributes = array(
                        ProjectTable::FIELD_CLIENT_ID => $project->getClient()->getId(),
                        //ProjectTable::FIELD_CODE => $project->getCode(),
                        ProjectTable::FIELD_NAME => $project->getName(),
                        ProjectTable::FIELD_DESCRIPTION => $project->getDescription(),
                        ProjectTable::FIELD_UPDATED_AT => new DateTime()
                    );
                    
                    $this->capsule->table(ProjectTable::NAME)
                        ->where(ProjectTable::FIELD_ID, '=', $project->getId())
                        ->update($attributes);
                    
                    $affetedRows ++;
                }
            }
            
            $connection->commit();
            
            return $affetedRows;
        } catch (Exception $e) {
            $connection->rollBack();
            throw $e;
        }
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
        
        return $this->capsule->table(ProjectTable::NAME)
            ->whereIn(ProjectTable::FIELD_ID, $ids)
            ->delete();
    }

    /**
     *
     * @param mixed $model
     * @param array $relations
     * @return BaseModel|NULL
     */
    public function format($model, array $relations = []): ?BaseModel
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