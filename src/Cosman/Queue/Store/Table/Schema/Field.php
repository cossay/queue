<?php
declare(strict_types = 1);
namespace Cosman\Queue\Store\Table\Schema;

class Field
{

    /**
     *
     * @var string
     */
    protected $database = '';

    /**
     *
     * @var string
     */
    protected $table = '';

    /**
     *
     * @var string
     */
    protected $name = '';

    /**
     *
     * @var string
     */
    protected $separator;

    /**
     *
     * @param string $field
     */
    public function __construct(string $field, string $separator = '.')
    {
        $this->separator = $separator;
        
        $components = explode($separator, $field);
        
        switch (count($components)) {
            case 1:
                $this->setName($components[0]);
                break;
            case 2:
                $this->setName($components[1]);
                $this->setTable($components[0]);
                break;
            case 3:
                $this->setName($components[2]);
                $this->setTable($components[1]);
                $this->setDatabase($components[0]);
                break;
        }
    }

    /**
     * Returns field name
     *
     * @return string|NULL
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Sets field name
     *
     * @param string $name
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = trim($name);
        
        return $this;
    }

    /**
     * Returns table name
     *
     * @return string|NULL
     */
    public function getTable(): ?string
    {
        return $this->table;
    }

    /**
     * Sets table name
     *
     * @param string $table
     * @return self
     */
    public function setTable(string $table): self
    {
        $this->table = trim($table);
        
        return $this;
    }

    /**
     * Returns database name
     *
     * @return string|NULL
     */
    public function getDatabase(): ?string
    {
        return $this->database;
    }

    /**
     * Sets database name
     *
     * @param string $database
     * @return self
     */
    public function setDatabase(string $database): self
    {
        $this->database = trim($database);
        
        return $this;
    }

    /**
     * Returns the full path of the field
     *
     * @return string
     */
    public function getPath(): string
    {
        return implode($this->separator, array_filter([
            $this->database,
            $this->table,
            $this->name
        ]));
    }

    public function __toString()
    {
        return $this->getPath();
    }
}