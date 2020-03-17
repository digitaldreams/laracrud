<?php

namespace LaraCrud\Repositories;

use DbReader\Column;
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
     * @var TableRepository
     */
    protected $table;

    /**
     * @param $name
     * @param string $table
     * @return ColumnContract
     */
    public function find($name, $table = ''): ColumnContract
    {
        $this->column = new Column($name, [], $table);
        $this->table = (new TableRepository())->find($table);
        return $this;
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
     * @return ForeignKeyContract|null
     */
    public function foreignKey(): ?ForeignKeyContract
    {
        if ($this->column->isForeign()) {
            return '';
        }
    }
}
