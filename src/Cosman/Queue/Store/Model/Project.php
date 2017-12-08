<?php
declare(strict_types = 1);
namespace Cosman\Queue\Store\Model;

use Cosman\Queue\Store\Table\ProjectTable;
use Cosman\Queue\Support\Reader\PropertyReader;
use Cosman\Queue\Store\Table\Schema\Field;

/**
 *
 * @author cosman
 *        
 */
class Project extends BaseModel
{

    /**
     *
     * @var Client
     */
    protected $client;

    /**
     *
     * @var string
     */
    protected $code;

    /**
     *
     * @var string
     */
    protected $name;

    /**
     *
     * @var string
     */
    protected $description;

    /**
     * Return project's client
     *
     * @return Client|NULL
     */
    public function getClient(): ?Client
    {
        return $this->client;
    }

    /**
     * Sets project's client
     *
     * @param Client $client
     * @return self
     */
    public function setClient(?Client $client): self
    {
        $this->client = $client;
        
        return $this;
    }

    /**
     * Returns project code
     *
     * @return string|NULL
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * Sets project code
     *
     * @param string $code
     * @return self
     */
    public function setCode(?string $code): self
    {
        $this->code = $code;
        
        return $this;
    }

    /**
     * Returns project name
     *
     * @return string|NULL
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Sets project name
     *
     * @param string $name
     * @return self
     */
    public function setName(?string $name): self
    {
        $this->name = $name;
        
        return $this;
    }

    /**
     * Returns project description
     *
     * @return string|NULL
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Sets project description
     *
     * @param string $description
     * @return self
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;
        
        return $this;
    }

    /**
     * Checks if a given job is owned by project
     *
     * This method does not hit the repository or database
     *
     * @param Job $job
     * @return bool
     */
    public function ownsJob(?Job $job): bool
    {
        return (null !== $job) && ($job->getProject() instanceof Project) && ($this->getId() === $job->getProject()->getId());
    }

    /**
     * Creates a new instace from a given value
     *
     * @param mixed $model
     * @param string $prefix
     * @param string $suffix
     * @param mixed $default
     * @return BaseModel|NULL
     */
    public static function createInstance($model, string $prefix = '', string $suffix = '', $default = null): ?BaseModel
    {
        $fields = ProjectTable::definition()->getFields();
        $reader = new PropertyReader($model, $prefix, $suffix);
        $defaultField = new Field('');
        
        $idField = $fields->get(ProjectTable::FIELD_ID, $defaultField)->getName();
        
        $id = (int) $reader->read($idField);
        
        $nameField = $fields->get(ProjectTable::FIELD_NAME, $defaultField)->getName();
        $descriptionField = $fields->get(ProjectTable::FIELD_DESCRIPTION, $defaultField)->getName();
        $codeField = $fields->get(ProjectTable::FIELD_CODE, $defaultField)->getName();
        $createdAtField = $fields->get(ProjectTable::FIELD_CREATED_AT, $defaultField)->getName();
        $updatedAtField = $fields->get(ProjectTable::FIELD_UPDATED_AT, $defaultField)->getName();
        
        if (empty($id)) {
            return $default;
        }
        
        $instance = new static();
        $instance->setId($id);
        $instance->setCode((string) $reader->read($codeField));
        $instance->setName((string) $reader->read($nameField));
        $instance->setDescription((string) $reader->read($descriptionField));
        $instance->setCreatedAt($instance->createDatetime((string) $reader->read($createdAtField)));
        $instance->setUpdatedAt($instance->createDatetime((string) $reader->read($updatedAtField)));
        
        return $instance;
    }
}