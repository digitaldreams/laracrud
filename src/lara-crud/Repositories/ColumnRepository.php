<?php

namespace LaraCrud\Repositories;

use DbReader\Column;
use DbReader\Table;
use LaraCrud\Contracts\ColumnContract;
use LaraCrud\Contracts\ForeignKeyContract;
use LaraCrud\Contracts\TableContract;

class ColumnRepository implements ColumnContract
{
    /**
     * @var Column;
     */
    protected $column;

    /**
     * @var TableContract
     */
    protected $table;

    /**
     * @param mixed        $data
     * @param string|Table $table
     */
    public function __construct($data, $table)
    {
        $this->table = $table instanceof Table ? $table : new Table($table);
        $this->column = new Column($data, [], $table);

        $files = $this->table->fileColumns();
        $file = isset($files[$this->column->name()]) ? $files[$this->column->name()] : '';
        $this->column->setFile($file);

        $relations = $this->table->relations();
        $this->column->foreign = isset($relations[$this->column->name()]) ? $relations[$this->column->name()] : [];
    }

    /**
     * @return bool
     */
    public function isPk(): bool
    {
        return $this->column->isPk();
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->column->name();
    }

    /**
     * @return string
     */
    public function label(): string
    {
        return $this->column->label();
    }

    /**
     * @return TableContract
     */
    public function table(): TableContract
    {
        return $this->table;
    }

    /**
     * @return bool
     */
    public function isFillable(): bool
    {
        return !$this->column->isProtected();
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return !$this->column->isNull();
    }

    /**
     * @return bool
     */
    public function isNull(): bool
    {
        return $this->column->isNull();
    }

    /**
     * @return bool
     */
    public function isUnique(): bool
    {
        return $this->column->isUnique();
    }

    /**
     * @return string
     */
    public function dataType(): string
    {
        return $this->column->type();
    }

    /**
     * @return string
     */
    public function inputType(): string
    {
        // TODO: Implement inputType() method.
    }

    /**
     * @return mixed
     */
    public function length()
    {
        return $this->column->length();
    }

    /**
     * @return int|null
     */
    public function order(): ?int
    {
        // TODO: Implement order() method.
    }

    /**
     * @param int $number
     *
     * @return mixed
     */
    public function setOrder(int $number): ColumnContract
    {
        // TODO: Implement setOrder() method.
    }

    /**
     * @return mixed
     */
    public function defaultValue()
    {
        return $this->column->defaultValue();
    }

    /**
     * @return string
     */
    public function image(): string
    {
        return $this->column->isFile();
    }

    /**
     * @return string
     */
    public function file(): string
    {
        return $this->column->isFile();
    }

    /**
     * @return array|null
     */
    public function options(): ?array
    {
        return $this->column->options();
    }

    /**
     * @return bool
     */
    public function isForeign(): bool
    {
        return $this->column->isForeign();
    }

    /**
     * @return ForeignKeyContract|null
     */
    public function foreignKey()
    {
        if ($this->column->isForeign()) {
            return $this->column;
        }

        return null;
    }
}
