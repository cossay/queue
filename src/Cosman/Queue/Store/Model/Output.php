<?php
declare(strict_types = 1);
namespace Cosman\Queue\Store\Model;

use Cosman\Queue\Store\Table\OutputTable;
use Cosman\Queue\Support\Reader\PropertyReader;
use Cosman\Queue\Store\Table\Schema\Field;

/**
 * Job output class
 *
 * @author cosman
 *        
 */
class Output extends BaseModel
{

    /**
     *
     * @var string
     */
    protected $code;

    /**
     *
     * @var Job
     */
    protected $job;

    /**
     *
     * @var mixed
     */
    protected $content;

    /**
     *
     * @var string[][]
     */
    protected $headers = [];

    /**
     *
     * @var int
     */
    protected $status_code;

    /**
     * Returns output unique code
     *
     * @return string|NULL
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * Sets output unique code
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
     *
     * @var string
     */
    protected $status_message;

    /**
     * Returns job
     *
     * @return Job|NULL
     */
    public function getJob(): ?Job
    {
        return $this->job;
    }

    /**
     * Sets job for output
     *
     * @param Job $job
     * @return self
     */
    public function setJob(?Job $job): self
    {
        $this->job = $job;
        
        return $this;
    }

    /**
     * Returns output content
     *
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Sets content for output
     *
     * @param mixed $content
     * @return self
     */
    public function setContent($content): self
    {
        $this->content = $content;
        
        return $this;
    }

    /**
     * Returns output headers
     *
     * @return string[][]
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Sets headers for output
     *
     * @param string[][] $headers
     * @return self
     */
    public function setHeaders(array $headers): self
    {
        $this->headers = $headers;
        
        return $this;
    }

    /**
     * Returns output status code
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->status_code;
    }

    /**
     * Sets status code for output
     *
     * @param int $code
     * @return self
     */
    public function setStatusCode(?int $code): self
    {
        $this->status_code = $code;
        
        return $this;
    }

    /**
     * Returns output status message
     *
     * @return string
     */
    public function getStatusMessage()
    {
        return $this->status_message;
    }

    /**
     * Sets status message for output
     *
     * @param string $message
     * @return self
     */
    public function setStatusMessage(?string $message): self
    {
        $this->status_message = $message;
        
        return $this;
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
        $tableFields = OutputTable::definition()->getFields();
        $reader = new PropertyReader($model, $prefix, $suffix);
        
        $fieldDefault = new Field('');
        
        $idField = $tableFields->get(OutputTable::FIELD_ID, $fieldDefault)->getName();
        
        $id = (int) $reader->read($idField);
        
        if (! $id) {
            return null;
        }
        
        $codeField = $tableFields->get(OutputTable::FIELD_CODE, $fieldDefault)->getName();
        $contentField = $tableFields->get(OutputTable::FIELD_CONTENT, $fieldDefault)->getName();
        $headersField = $tableFields->get(OutputTable::FIELD_HEADERS, $fieldDefault)->getName();
        $statusCodeField = $tableFields->get(OutputTable::FIELD_STATUS_CODE, $fieldDefault)->getName();
        $messageField = $tableFields->get(OutputTable::FIELD_STATUS_MESSAGE, $fieldDefault)->getName();
        $createdAtField = $tableFields->get(OutputTable::FIELD_CREATED_AT, $fieldDefault)->getName();
        $updatedAtField = $tableFields->get(OutputTable::FIELD_UPDATED_AT, $fieldDefault)->getName();
        
        $instance = new static();
        $instance->setId($id);
        $instance->setCode((string) $reader->read($codeField));
        $instance->setContent((string) $reader->read($contentField));
        
        $headers = $reader->read($headersField);
        
        if (is_string($headers)) {
            $headers = json_decode($headers, true);
            
            if (! is_array($headers)) {
                $headers = (array) $headers;
            }
            $instance->setHeaders($headers);
        }
        $instance->setStatusCode((int) $reader->read($statusCodeField));
        $instance->setStatusMessage((string) $reader->read($messageField));
        $instance->setCreatedAt($instance->createDatetime((string) $reader->read($createdAtField)));
        $instance->setUpdatedAt($instance->createDatetime((string) $reader->read($updatedAtField)));
        
        return $instance;
    }
}