<?php

namespace LaraCrud\Crud;

use LaraCrud\Builder\Model as ModelBuilder;
use LaraCrud\Contracts\Crud;
use LaraCrud\Contracts\TableContract;
use LaraCrud\Helpers\Helper;
use LaraCrud\Helpers\TemplateManager;

class Model implements Crud
{
    use Helper;

    /**
     * Model Namespace. If not specified then default namespace will be used.
     *
     * @var string
     */
    protected string $namespace;

    /**
     * Name of Model class.
     *
     * @var string
     */
    protected $modelName;

    /**
     * @var TableContract
     */
    protected $table;

    /**
     * @var ModelBuilder
     */
    protected $modelBuilder;

    /**
     * @var array
     */
    protected array $traits = ['HasFactory'];

    /**
     * @var array
     */
    protected array $importNamespaces = [];

    /**
     * @var array
     */
    protected $searchableColumns = '';

    /**
     * Model constructor.
     *
     * @param $table
     * @param $name string  user define model and namespace. E.g. Models/MyUser will be saved as App\Models\MyUser
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct($table, $name = '')
    {
        $this->table = app()->make(TableContract::class, ['table' => $table]);
        $this->modelBuilder = $this->makeModelBuilders();
        $this->namespace = $this->getFullNS(config('laracrud.model.namespace'));
        $this->modelName = $this->getModelName($table);

        if (!empty($name)) {
            $this->parseName($name);
        }
        $this->isSoftDeleteAble()->searchable();
    }

    /**
     * Done all processing work and make the final code that is ready to save as php file.
     *
     * @throws \Exception
     *
     * @return string
     */
    public function template()
    {
        $relations = $this->relations();
        $data = [
            'namespace' => $this->namespace,
            'modelName' => $this->modelName,
            'propertyDefiner' => config('laracrud.model.propertyDefiner') ? implode("\n", array_reverse($this->modelBuilder->propertyDefiners)) : '',
            'methodDefiner' => config('laracrud.model.methodDefiner') ? implode("\n", array_reverse($this->modelBuilder->methodDefiners)) : '',

            'tableName' => $this->table->name(),
            'constants' => $this->constants(),
            'guarded' => config('laracrud.model.guarded') ? $this->guarded() : '',
            'fillable' => config('laracrud.model.fillable') ? $this->fillable() : '',

            'dateColumns' => $this->dates(),
            'casts' => config('laracrud.model.casts') ? $this->casts() : '',
            'relationShips' => $relations,

            'mutators' => config('laracrud.model.mutators') ? $this->mutators() : '',
            'accessors' => config('laracrud.model.accessors') ? $this->accessors() : '',
            'scopes' => config('laracrud.model.scopes') ? $this->scopes() : '',
            'traits' => !empty($this->traits) ? 'use ' . implode(', ', $this->traits) . ';' : '',
            'importNamespaces' => !empty($this->importNamespaces) ? implode("\n", $this->importNamespaces) : '',
            'searchable' => $this->searchableColumns,
        ];
        $tempMan = new TemplateManager('model/template.txt', $data);

        return $tempMan->get();
    }

    /**
     * Save code as php file.
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function save()
    {
        $filePath = $this->checkPath();
        if (file_exists($filePath)) {
            throw new \Exception($this->namespace . '\\' . $this->modelName . ' already exists');
        }
        $model = new \SplFileObject($filePath, 'w+');
        $model->fwrite($this->template());
    }

    /**
     * Make constant code.
     *
     * @return mixed
     */
    protected function constants()
    {
        return implode("\n", $this->modelBuilder->constants);
    }

    /**
     * Generate guarded code.
     *
     * @return string
     */
    protected function guarded()
    {
        if (config('laracrud.model.guarded')) {
            $tempMan = new TemplateManager('model/guarded.txt');

            return $tempMan->get();
        }

        return '';
    }

    /**
     * Make fillable property.
     *
     * @return string
     */
    protected function fillable()
    {
        if (!config('laracrud.model.fillable')) {
            return '';
        }
        $tempMan = new TemplateManager('model/fillable.txt', [
            'columns' => implode(",\n", array_reverse($this->modelBuilder->fillable())),
        ]);

        return $tempMan->get();
    }

    /**
     * Making of dates property.
     *
     * @return string
     */
    protected function dates()
    {
        $tempMan = new TemplateManager('model/dates.txt', [
            'columns' => implode(",\n", array_reverse($this->modelBuilder->dates)),
        ]);

        return $tempMan->get();
    }

    /**
     * Making of casts property.
     *
     * @return string
     */
    protected function casts()
    {
        $tempMan = new TemplateManager('model/casts.txt', [
            'columns' => implode(",\n", array_reverse($this->modelBuilder->casts())),
        ]);

        return $tempMan->get();
    }

    /**
     * Making relationship code.
     *
     * @throws \Exception
     *
     * @return string
     */
    protected function relations()
    {
        $temp = '';
        $relations = $this->table->relations();

        foreach ($relations as $relation) {
            $tempMan = new TemplateManager('model/relationship.txt', [
                'relationShip' => $relation['relationShip'],
                'modelName' => $relation['modelName'],
                'methodName' => $relation['methodName'],
                'returnType' => $relation['returnType'],
                'params' => $relation['params'],
            ]);
            $temp .= $tempMan->get() . PHP_EOL;
            array_unshift($this->modelBuilder->propertyDefiners, $relation['propertyDefiners']);
        }

        return $temp;
    }

    /**
     * making of scopes methods code.
     *
     * @return string
     */
    protected function scopes()
    {
        $tempMan = new TemplateManager('model/search_scope.txt', [
            'whereClause' => implode("\n", $this->modelBuilder->makeSearch()),
        ]);
        $scopes = implode("\n", array_reverse($this->modelBuilder->scopes));

        return $scopes . PHP_EOL . $tempMan->get();
    }

    /**
     * Making of mutators code.
     *
     * @return string
     */
    protected function mutators()
    {
        return implode("\n", array_reverse($this->modelBuilder->mutators));
    }

    /**
     * Making of accessors method code.
     *
     * @return string
     */
    protected function accessors()
    {
        return implode("\n", array_reverse($this->modelBuilder->accessors));
    }

    /**
     * @return \LaraCrud\Builder\Model
     */
    public function makeModelBuilders()
    {
        $builder = null;
        $columns = $this->table->columns();

        foreach ($columns as $column) {
            if (empty($builder)) {
                $builder = new ModelBuilder($column);
            } else {
                $newBuilder = new ModelBuilder($column);
                $newBuilder->merge($builder);
                $builder = $newBuilder;
            }
        }

        return $builder;
    }

    /**
     * @return string
     */
    public function modelName()
    {
        return $this->modelName;
    }

    /**
     * @return string
     */
    public function getFullModelName()
    {
        return $this->namespace . '\\' . $this->modelName;
    }

    protected function isSoftDeleteAble()
    {
        if ($this->table->isSoftDeleteAble()) {
            $this->traits[] = 'SoftDeletes';
            $this->importNamespaces[] = 'use Illuminate\Database\Eloquent\SoftDeletes;';
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function searchable()
    {
        $arr = null;
        if ($columns = $this->table->searchableColumns()) {
            foreach ($columns as $column) {
                $arr .= "\t'" . $column . "'," . PHP_EOL;
            }

            $this->traits[] = 'FullTextSearch';
            $this->importNamespaces[] = 'use LaraCrud\Services\FullTextSearch;';
            $this->searchableColumns = "\t" . 'protected $searchable = [' . "\n" . $arr . '];';
        }

        return $this;
    }
}
