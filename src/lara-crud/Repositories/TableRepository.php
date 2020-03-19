<?php

namespace LaraCrud\Repositories;

use DbReader\Table;
use Illuminate\Support\Str;
use LaraCrud\Contracts\DatabaseContract;
use LaraCrud\Contracts\TableContract;
use LaraCrud\Helpers\ForeignKey;

class TableRepository implements TableContract
{

    /**
     * @var table
     */
    protected $table;

    /**
     * TableRepository constructor.
     *
     * @param $table
     */
    public function __construct($table)
    {
        $this->table = new Table($table);
    }

    /**
     * @param $name
     *
     * @return bool
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function exists(): bool
    {
        $dpRepo = app()->make(DatabaseContract::class);

        return $dpRepo->tableExists($this->table->name());
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
        $arr = [];
        foreach ($this->table->columns() as $name => $data) {
            $arr[] = new ColumnRepository($data, $this->table);
        }

        return $arr;
    }

    /**
     * @return array
     */
    public function indexes(): array
    {
        return $this->table->indexes();
    }

    /**
     * @return array
     */
    public function relations(): array
    {
        $otherKeys = $this->table->references();
        $relations = [];
        foreach ($otherKeys as $column) {
            $fk = new ForeignKey($column);
            $relation = [
                'modelName' => $fk->modelName(),
                'methodName' => Str::plural(lcfirst($fk->modelName())),
            ];
            if ($fk->isPivot) {
                $relation['params'] = ",'" . $fk->table() . "'";
                $relation['relationShip'] = ForeignKey::RELATION_BELONGS_TO_MANY;
                $relation['returnType'] = ucfirst(ForeignKey::RELATION_BELONGS_TO_MANY);
            } else {
                $relation['params'] = ",'" . $fk->column() . "'";
                $relation['relationShip'] = ForeignKey::RELATION_HAS_MANY;
                $relation['returnType'] = ucfirst(ForeignKey::RELATION_HAS_MANY);
            }
            $relations[] = $relation;
        }
        foreach ($this->table->relations() as $foreign) {
            $fk = new ForeignKey($foreign);
            $relations[] = [
                'name' => ForeignKey::RELATION_BELONGS_TO,
                'foreign_key' => $fk->column(),
                'model' => ucfirst(Str::camel(Str::singular($fk->foreignTable()))),
                'methodName' => Str::camel(Str::singular($fk->foreignTable())),
                'other_key' => $fk->foreignColumn(),
            ];
        }

        return $relations;
    }

    /**
     * @return array
     */
    public function references(): array
    {
        return $this->table->references();
    }

    /**
     * @return array
     */
    public function fileColumns(): array
    {
        return $this->table->fileColumns();
    }

    /**
     * @return bool
     */
    public function hasFile(): bool
    {
        return count($this->table->hasFile());
    }
}
