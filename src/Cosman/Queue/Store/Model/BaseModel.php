<?php
declare(strict_types = 1);
namespace Cosman\Queue\Store\Model;

use Cosman\Queue\Support\DateTime\DateTime;
use Exception;

/**
 *
 * @author cosman
 *        
 */
class BaseModel implements \JsonSerializable
{

    /**
     *
     * @var int
     */
    protected $id;

    /**
     *
     * @var DateTime
     */
    protected $created_at;

    /**
     *
     * @var DateTime
     */
    protected $updated_at;

    /**
     * Returns model id
     *
     * @return int|NULL
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Sets model id
     *
     * @param int $id
     * @return self
     */
    public function setId(?int $id): self
    {
        $this->id = $id;
        
        return $this;
    }

    /**
     * Returns date and time when model was created
     *
     * @return DateTime|NULL
     */
    public function getCreatedAt(): ?DateTime
    {
        return $this->created_at;
    }

    /**
     * Sets date and time when model was created
     *
     * @param DateTime $datetime
     * @return self
     */
    public function setCreatedAt(?DateTime $datetime): self
    {
        $this->created_at = $datetime;
        
        return $this;
    }

    /**
     * Returns date and time when model was last updated
     *
     * @return DateTime|NULL
     */
    public function getUpdatedAt(): ?DateTime
    {
        return $this->updated_at;
    }

    /**
     * Sets date and time when model was last updated
     *
     * @param DateTime $datetime
     * @return self
     */
    public function setUpdatedAt(?DateTime $datetime): self
    {
        $this->updated_at = $datetime;
        
        return $this;
    }

    /**
     * Converts model into an array
     *
     * @return array
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    /**
     * Removes all attributes considered sensitive that should be removed when converting model to json
     *
     * @param array $attributes
     * @return array
     */
    public function removeSensitiveAttributes(array $attributes): array
    {
        return $attributes;
    }

    /**
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->removeSensitiveAttributes($this->toArray());
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
     * Cleans header array by removing unnecessary characters from header names
     *
     * @param mixed $headers
     * @return array
     */
    protected function cleanHeaders($headers): array
    {
        $cleanedHeaders = [];
        
        if (is_array($headers)) {
            foreach ($headers as $name => $values) {
                $key = str_replace([
                    '[',
                    ']'
                ], '', $name);
                
                $cleanedHeaders[$key] = $values;
            }
        }
        
        return $cleanedHeaders;
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
        return new static();
    }
}