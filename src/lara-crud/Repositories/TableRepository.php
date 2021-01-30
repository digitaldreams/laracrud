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
     * @param int|string $table
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
     * List of ColumnRepository.
     *
     * @return \LaraCrud\Repositories\ColumnRepository[]
     * @throws \Exception
     */
    public function columns(): array
    {
        $arr = [];
        $foreignColumns = $this->table->relations();
        foreach ($this->table->columns() as $name => $data) {
            $foreignColumn = isset($foreignColumns[$name]) ? $foreignColumns[$name] : [];
            $arr[$name] = new ColumnRepository($data, $this, $foreignColumn);
        }

        return $arr;
    }

    /**
     * Create Relationships array.
     *
     * @return array
     *
     * @throws \Exception
     */
    public function relations(): array
    {
        return array_merge($this->belongsToRelations(), $this->hasManyAndBelongsToManyRelations());
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
        return count($this->table->fileColumns());
    }

    /**
     * @return bool
     */
    public function isSoftDeleteAble(): bool
    {
        return array_key_exists('deleted_at', $this->table->columns());
    }

    /**
     * FullText SearchAble Columns.
     *
     * @return array
     */
    public function searchableColumns(): array
    {
        $data = [];
        $indexes = $this->table->indexes();
        foreach ($indexes as $index) {
            if ('FULLTEXT' == $index->Index_type) {
                $data[$index->Seq_in_index] = $index->Column_name;
            }
        }

        return $data;
    }

    /**
     * Create Belongs To Relationship.
     *
     * @throws \Exception
     */
    protected function belongsToRelations(): array
    {
        $relations = [];
        foreach ($this->table->relations() as $foreign) {
            $fk = new ForeignKey($foreign);
            $modelName = ucfirst(Str::camel(Str::singular($fk->foreignTable())));
            $methodName = Str::camel(Str::singular($fk->foreignTable()));
            $belongToRelation = [
                'relationShip' => ForeignKey::RELATION_BELONGS_TO,
                'returnType' => ucfirst(ForeignKey::RELATION_BELONGS_TO),
                'modelName' => $modelName,
                'methodName' => $methodName,
                'params' => ",'" . $fk->column() . "','" . $fk->foreignColumn() . "'",
                'propertyDefiners' => '@property ' . $modelName . ' $' . $methodName . ' ' . ucfirst(ForeignKey::RELATION_BELONGS_TO),
            ];
            if (isset($relations[$methodName])) {
                $relations[$methodName . rand(1, 5)] = $belongToRelation;
            } else {
                $relations[$methodName] = $belongToRelation;
            }
        }

        return $relations;
    }

    /**
     * Create HasMany and BelongsToMany Relationships.
     *
     * @return array
     * @throws \Exception
     */
    protected function hasManyAndBelongsToManyRelations(): array
    {
        $otherKeys = $this->table->references();
        $relations = [];
        foreach ($otherKeys as $column) {
            $fk = new ForeignKey($column);
            $methodName = Str::plural(lcfirst($fk->modelName()));
            $relation = [
                'modelName' => $fk->modelName(),
                'methodName' => $methodName,
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

            $relation['propertyDefiners'] = '@property \Illuminate\Database\Eloquent\Collection' . ' $' . $relation['methodName'] . ' ' . $relation['relationShip'];

            if (isset($relations[$methodName])) {
                $relations[$methodName . rand(1, 5)] = $relation;
            } else {
                $relations[$methodName] = $relation;
            }
        }

        return $relations;
    }

    /**
     * Get Database Table object.
     *
     * @return Table
     */
    public function getTable(): Table
    {
        return $this->table;
    }
}
