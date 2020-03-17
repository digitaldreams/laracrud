<?php

namespace LaraCrud\Repositories;

use DbReader\Table;
use LaraCrud\Contracts\DatabaseContract;
use LaraCrud\Contracts\TableContract;

class TableRepository implements TableContract
{
    /**
     * @var Table
     */
    protected $table;

    /**
     * @param $name
     *
     * @return TableContract
     */
    public function find($name): TableContract
    {
        $this->table = new Table($name);

        return $this;
    }

    /**
     * @param $name
     * @return bool
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function exists($name): bool
    {
        $dpRepo = app()->make(DatabaseContract::class);

        return  $dpRepo->tableExists($name);
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->table->name();
    }

    /**
     * @return string
     */
    public function label(): string
    {
        return ucwords(str_replace('_', ' ', $this->table->name()));
    }

    /**
     * @return string
     */
    public function icon(): string
    {
        return 'fa fa-list';
    }

    /**
     * @return object|null
     */
    public function model(): ?object
    {
        // TODO: Implement model() method.
    }

    /**
     * @return array
     */
    public function columns(): array
    {
    }

    /**
     * @return array
     */
    public function indexes(): array
    {
        // TODO: Implement indexes() method.
    }

    /**
     * @return array
     */
    public function relations(): array
    {
        // TODO: Implement relations() method.
    }

    /**
     * @return array
     */
    public function references(): array
    {
        // TODO: Implement references() method.
    }

    /**
     * @return array
     */
    public function fileColumns(): array
    {
        // TODO: Implement fileColumns() method.
    }

    /**
     * @return bool
     */
    public function hasFile(): bool
    {
        // TODO: Implement hasFile() method.
    }
}
