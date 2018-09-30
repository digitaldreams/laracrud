<?php
/**
 * Tuhin Bepari <digitaldreams40@gmail.com>
 */

namespace LaraCrud\Crud;

use DbReader\Table;
use LaraCrud\Builder\Model as ModelBuilder;
use LaraCrud\Contracts\Crud;
use LaraCrud\Helpers\ForeignKey;
use LaraCrud\Helpers\Helper;
use LaraCrud\Helpers\TemplateManager;


class Model implements Crud
{
    use Helper;
    /**
     * Model Namespace. If not specified then default namespace will be used.
     * @var string
     */
    protected $namespace;

    /**
     * The Namespace for the trait
     * @var string
     */
    protected $traitSpace;

    /**
     * Name of Model class
     * @var string
     */
    protected $modelName;

    /**
     * Name of Trait
     * @var string
     */
    protected $traitName;

    /**
     * @var Table
     */
    protected $table;

    /**
     * @var array
     */
    protected $traits = [];

    /**
     * @var ModelBuilder
     */
    protected $modelBuilder;

    /**
     * Parent Eloquent class
     * @var string
     */
    protected $eloquent;

    /**
     * Model constructor.
     * @param $table
     * @param $name  user define model and namespace. E.g. Models/MyUser will be saved as App\Models\MyUser
     */
    public function __construct($table, $name = '')
    {
        $this->table = new Table($table);
        $this->modelBuilder = $this->makeModelBuilders();
        $this->namespace = $this->getFullNS(config('laracrud.model.namespace'));
        $this->traitSpace = $this->getFullNS(config('laracrud.model.traitNamespace'));
        $this->modelName = $this->getModelName($table);
        $this->traitName = 'Trait' . $this->modelName;
        if (!empty($name)) {
            $this->parseName($name);
        }
    }

    /**
     * Done all processing work and make the final code that is ready to save as php file
     * @return string
     */
    public function template()
    {
        $this->setTraits();
        $relations = $this->relations();
        $data = [
            'imports' => $this->imports(),
            'eloquentbase' => $this->eloquentBase(),
            'uses' => $this->uses(),
            'namespace' => $this->namespace,
            'timestamp' => var_export($this->modelBuilder->enableTimestamps(), true),
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
            'scopes' => config('laracrud.model.scopes') ? $this->scopes() : ''
        ];
        $tempMan = new TemplateManager('model/template.txt', $data);
        return $tempMan->get();
    }

    /**
     * Save code as php file
     * @return mixed
     * @throws \Exception
     */
    public function save()
    {
        $filePath = $this->checkPath();
        if (file_exists($filePath)) {
            throw new \Exception($this->namespace . '\\' . $this->modelName . ' already exists');
        }
        $model = new \SplFileObject($filePath, 'w+');
        $model->fwrite($this->template());
        $this->createTrait();
    }

    public function traitTemplate()
    {
        $data = [
            'modelName' => $this->modelName,
            'namespace' => $this->traitSpace,
            'traitName' => $this->traitName,
        ];
        return (new TemplateManager('model/trait_template.txt', $data))->get();
    }

    private function createTrait()
    {
        $filePath = $this->traitPath($this->traitSpace);
        if (!file_exists($filePath)) {
            $model = new \SplFileObject($filePath, 'w+');
            $model->fwrite($this->traitTemplate());
        }
    }

    /**
     * Make constant code
     * @return mixed
     */
    protected function constants()
    {
        return implode("\n", $this->modelBuilder->constants);
    }

    /**
     * Generate guarded code
     * @return string
     */
    protected function guarded()
    {
        if (config('laracrud.model.guarded') && !empty($this->modelBuilder->guarded)) {
            $tempMan = new TemplateManager('model/guarded.txt', ['guarded' => implode(', ', $this->modelBuilder->guarded)]);
            return $tempMan->get();
        }
        return '';
    }

    /**
     * Make fillable property
     * @return string
     */
    protected function fillable()
    {
        if (!config('laracrud.model.fillable')) {
            return '';
        }
        $tempMan = new TemplateManager('model/fillable.txt', ['columns' => implode(",", array_reverse($this->modelBuilder->fillable))]);
        return $tempMan->get();
    }

    /**
     * Making of dates property
     * @return string
     */
    protected function dates()
    {
        $tempMan = new TemplateManager('model/dates.txt', ['columns' => implode(",", array_reverse($this->modelBuilder->dates))]);
        return $tempMan->get();
    }

    /**
     * Making of casts property
     * @return string
     */
    protected function casts()
    {
        $tempMan = new TemplateManager('model/casts.txt', ['columns' => implode(",", array_reverse($this->modelBuilder->casts))]);
        return $tempMan->get();
    }

    protected function setTraits()
    {
        if (!config('laracrud.model.modelTraits')) return;
        $me = $this->traitSpace . '\\' . $this->traitName;
        $this->traits[basename($me)] = $me;
        $others = config('laracrud.model.tableUse');
        if (is_array($others) && !empty($others[$this->table->name()])) {
            if (is_array($others[$this->table->name()])) {
                foreach ($others[$this->table->name()] as $trait) {
                    $this->traits[basename($trait)] = $trait;
                }
            } else {
                $this->traits[basename($others[$this->table->name()])] = $others[$this->table->name()];
            }
        }
    }

    protected function eloquentBase()
    {
        return basename(config('laracrud.model.eloquent'));
    }

    protected function imports()
    {
        $uses[] = (new TemplateManager('model/imports.txt', ['path' => config('laracrud.model.eloquent')]))->get();
        foreach ($this->traits as $trait) {
            $uses[] = (new TemplateManager('model/imports.txt', ['path' => $trait]))->get();
        }
        return implode(PHP_EOL, $uses);
    }

    protected function uses()
    {
        if (empty($this->traits)) return '';
        return 'use ' . implode(', ', array_keys($this->traits)) . ';';
    }

    /**
     * Making relationship code
     * @return string
     */
    protected function relations()
    {
        $temp = '';
        $otherKeys = $this->table->references();
        //print_r($this->modelBuilder->relations);
        foreach ($this->modelBuilder->relations as $relation) {
            $param = ",'" . $relation['foreign_key'] . "'";
            $tempMan = new TemplateManager('model/relationship.txt', [
                'relationShip' => $relation['name'],
                'modelName' => $relation['model'],
                'methodName' => lcfirst($relation['model']),
                'returnType' => ucfirst($relation['name']),
                'params' => $param
            ]);
            $temp .= $tempMan->get() . PHP_EOL;
            array_unshift($this->modelBuilder->propertyDefiners, ' * @property ' . $relation['model'] . ' $' . lcfirst($relation['model']) . ' ' . $relation['name']);
        }
        foreach ($otherKeys as $column) {
            $fk = new ForeignKey($column);

            if ($fk->isPivot) {
                $param = ",'" . $fk->table() . "'";
                $tempMan = new TemplateManager('model/relationship.txt', [
                    'relationShip' => ForeignKey::RELATION_BELONGS_TO_MANY,
                    'modelName' => $fk->modelName(),
                    'methodName' => str_plural(lcfirst($fk->modelName())),
                    'returnType' => ucfirst(ForeignKey::RELATION_BELONGS_TO_MANY),
                    'params' => $param
                ]);
                array_unshift($this->modelBuilder->propertyDefiners, ' * @property \Illuminate\Database\Eloquent\Collection' . $fk->modelName() . '[]' . ' $' . lcfirst($fk->modelName()) . ' ' . ForeignKey::RELATION_BELONGS_TO_MANY);
            } else {
                $param = ",'" . $fk->column() . "'";
                $tempMan = new TemplateManager('model/relationship.txt', [
                    'relationShip' => ForeignKey::RELATION_HAS_MANY,
                    'modelName' => $fk->modelName(),
                    'methodName' => str_plural(lcfirst($fk->modelName())),
                    'returnType' => ucfirst(ForeignKey::RELATION_HAS_MANY),
                    'params' => $param
                ]);
                array_unshift($this->modelBuilder->propertyDefiners, ' * @property \Illuminate\Database\Eloquent\Collection|' . $fk->modelName() . '[]' . ' $' . lcfirst($fk->modelName()) . ' ' . ForeignKey::RELATION_HAS_MANY);
            }
            $temp .= $tempMan->get();

        }
        return $temp;
    }

    /**
     * making of scopes methods code
     * @return string
     */
    protected function scopes()
    {
        $tempMan = new TemplateManager('model/search_scope.txt', ['whereClause' => implode("\n", $this->modelBuilder->makeSearch())]);
        $scopes = implode("\n", array_reverse($this->modelBuilder->scopes));
        return $scopes . PHP_EOL . $tempMan->get();
    }

    /**
     * Making of mutators code
     * @return string
     */
    protected function mutators()
    {
        return implode("\n", array_reverse($this->modelBuilder->mutators));
    }

    /**
     * Making of accessors method code
     * @return string
     */
    protected function accessors()
    {
        return implode("\n", array_reverse($this->modelBuilder->accessors));
    }

    /**
     *
     */
    public function makeModelBuilders()
    {
        $builder = null;
        $columns = $this->table->columnClasses();

        $cols = (array)$columns;
        $builders = new ModelBuilder(array_shift($cols));
        foreach ($cols as $column) {
            $b = new ModelBuilder($column);
            $builders->merge($b);
        }
        return $builders;
    }

    public function modelName()
    {
        return $this->modelName;
    }


}