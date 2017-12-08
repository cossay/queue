<?php
declare(strict_types = 1);
namespace Cosman\Queue\Store\Repository;

use Illuminate\Database\Capsule\Manager;
use Exception;
use Cosman\Queue\Store\Model\BaseModel;
use Cosman\Queue\Support\DateTime\DateTime;
use Cosman\Queue\Store\Table\Schema\Field;

/**
 * Base repository class
 *
 * @author cosman
 *        
 */
abstract class BaseRepository
{

    /**
     *
     * @var Manager
     */
    protected $capsule;

    /**
     *
     * @param Manager $capsule
     */
    public function __construct(Manager $capsule)
    {
        $this->capsule = $capsule;
    }

    /**
     * Formats a collection of models
     *
     * @param iterable $collection
     * @param string[] $relations
     * @return iterable
     */
    public function formatCollection(iterable $collection, array $relations = []): iterable
    {
        $formattedCollection = [];
        
        foreach ($collection as $model) {
            $formatedModel = $this->format($model);
            
            if ($formatedModel) {
                $formattedCollection[] = $formatedModel;
            }
        }
        
        return $formattedCollection;
    }

    /**
     * Formats a single record
     *
     * @param mixed $model
     * @param string[] $relations
     */
    abstract public function format($model, array $relations = []): ?BaseModel;

    /**
     * Reads an attribute from a given value
     *
     * The value must be either an array or an object
     *
     * @param mixed $model
     * @param string $field
     * @param string $prefix
     * @param mixed $default
     * @return mixed
     */
    protected function readAttribute($model, string $field, string $prefix = '', $default = null)
    {
        $key = $prefix . $field;
        
        if (is_array($model)) {
            return $model[$key] ?? $default;
        }
        
        if (is_object($model)) {
            return $model->{$key} ?? $default;
        }
        
        return $default;
    }

    /**
     * Creates a datetime instance from a string
     *
     * @param string $datetime
     * @return \Cosman\Queue\Support\DateTime\DateTime|NULL
     */
    protected function createDatetime(?string $datetime): ?DateTime
    {
        if (! $datetime) {
            return null;
        }
        
        try {
            return new DateTime($datetime);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Returns a string array of selected fields
     *
     * @return string[]
     */
    protected function createSelectableFieldList(): array
    {
        $fields = [];
        
        foreach ($this->getSelectableFields() as $field) {
            if ($field instanceof Field) {
                $fields[] = $field->getPath();
            }
        }
        
        return array_unique($fields);
    }

    /**
     * Returns all selectable fiels
     *
     * @return array
     */
    abstract protected function getSelectableFields(): array;
}