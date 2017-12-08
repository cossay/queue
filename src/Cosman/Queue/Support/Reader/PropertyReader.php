<?php
declare(strict_types = 1);
namespace Cosman\Queue\Support\Reader;

/**
 *
 * @author cosman
 *        
 */
class PropertyReader
{

    /**
     *
     * @var mixed
     */
    protected $data;

    /**
     *
     * @var string
     */
    protected $prefix = '';

    /**
     *
     * @var string
     */
    protected $suffix = '';

    /**
     *
     * @param mixed $data
     * @param string $prefix
     * @param string $suffix
     */
    public function __construct($data, string $prefix = '', string $suffix = '')
    {
        $this->data = $data;
        
        $this->prefix = trim($prefix);
        
        $this->suffix = trim($suffix);
    }

    /**
     * Reads an attribute of a given value regardless of type
     *
     * @param string $path
     * @param mixed $default
     */
    public function read(string $path, $default = null)
    {
        $value = null;
        
        switch (gettype($this->data)) {
            case 'array':
                $value = $this->readArray($path, $default);
                break;
            case 'object':
                $value = $this->readObject($path, $default);
                break;
            default:
                $value = $default;
        }
        
        return $value;
    }

    /**
     * Reads an index of an array
     *
     * @param string $path
     * @param mixed $default
     */
    public function readArray(string $path, $default = null)
    {
        if (! is_array($this->data)) {
            return $default;
        }
        
        $key = $this->prefix . $path . $this->suffix;
        
        return $this->data[$key] ?? $default;
    }

    /**
     * Reads a property of an object
     *
     * @param string $path
     * @param mixed $default
     */
    public function readObject(string $path, $default = null)
    {
        if (! is_object($this->data)) {
            return $default;
        }
        
        $key = $this->prefix . $path . $this->suffix;
        
        return $this->data->{$key} ?? $default;
    }
}