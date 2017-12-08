<?php
declare(strict_types = 1);
namespace Cosman\Queue\Store\Table\Schema;

use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Database table representation class
 *
 * @author cosman
 *        
 */
class Table
{

    const TABLE_FIELD_NAME = 'NAME';

    /**
     *
     * @var string
     */
    protected $name = '';

    /**
     *
     * @var ParameterBag
     */
    protected $fields;

    /**
     *
     * @param string $tableClass
     */
    public function __construct(string $tableClass)
    {
        $this->fields = new ParameterBag();
        
        $reflection = new \ReflectionClass($tableClass);
        
        $definition = new ParameterBag($reflection->getConstants());
        
        foreach ($definition as $key => $field) {
            if (self::TABLE_FIELD_NAME != $key) {
                $this->fields->set($field, new Field($field));
            } else {
                $this->name = $field;
            }
        }
    }

    /**
     *
     * @return self
     */
    protected function copy(): self
    {
        return clone $this;
    }

    /**
     * Return table name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns table fields
     *
     * @return \Symfony\Component\HttpFoundation\ParameterBag
     */
    public function getFields(): ParameterBag
    {
        return $this->fields;
    }

    /**
     * Returns table fields with each field alias
     *
     * @param string $prefix
     * @param string $suffix
     * @return \Symfony\Component\HttpFoundation\ParameterBag
     */
    public function aliasFields(string $prefix, string $suffix = ''): ParameterBag
    {
        $fields = new ParameterBag();
        
        foreach ($this->fields as $key => $field) {
            if ($field instanceof Field) {
                $tempField = clone $field;
                
                $tempField->setName($field->getName() . ' AS ' . $prefix . $field->getName() . $suffix);
                
                $fields->set($key, $tempField);
            }
        }
        
        return $fields;
    }

    /**
     * Create an alias
     *
     * @param string $tableAlias
     * @param string $fieldPrefiex
     * @return \Cosman\Queue\Store\Table\Schema\Table
     */
    public function createAlias(string $tableAlias, string $fieldPrefiex = '')
    {
        $fieldPrefiex = $fieldPrefiex ?? $tableAlias;
        
        $alias = $this->copy();
        
        $alias->name = $tableAlias;
        
        foreach ($alias->fields as $key => $field) {
            if ($field instanceof Field) {
                $name = $fieldPrefiex . $field->getName();
                
                $alias->fields->get($key)
                    ->setTable($alias->name)
                    ->setName($name);
            }
        }
        
        return $alias;
    }
}