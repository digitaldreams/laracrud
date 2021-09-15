<?php

namespace LaraCrud\Crud\ReactJs;

use Illuminate\Database\Eloquent\Model;
use LaraCrud\Contracts\Crud;
use LaraCrud\Contracts\TableContract;
use LaraCrud\Helpers\TemplateManager;
use LaraCrud\Services\ModelRelationReader;

class ReactJsModelCrud implements Crud
{
    protected ModelRelationReader $modelRelationReader;

    /**
     * @var Model
     */
    protected $model;

    /**
     * @var \LaraCrud\Contracts\TableContract|\Illuminate\Contracts\Foundation\Application|mixed
     */
    protected TableContract $table;

    protected string $shortName;

    /**
     * @var array
     */
    protected array $imports = [];

    protected array $properties = [];

    protected array $belongsToModels = [];

    /**
     * @throws \ReflectionException
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->modelRelationReader = (new ModelRelationReader($model))->read();
        $this->table = app(TableContract::class, ['table' => $model->getTable()]);
        $this->shortName = (new \ReflectionClass($this->model))->getShortName();
        $this->process();
    }

    public function template()
    {
        return (new TemplateManager('reactjs/model.txt', [
            'model' => $this->shortName,
            'destruction' => $this->prepareDestruction(),
            'propertyAssignment' => $this->preparePropertyAssignment(),
            'propertySetters' => $this->preparePropertySetters(),
            'import' => implode("\n", array_unique($this->imports)),
        ]))->get();
    }

    protected function process()
    {
        foreach ($this->table->columns() as $column) {
            $this->properties[$column->name()] = $column->defaultValue();
        }
        foreach ($this->modelRelationReader->getSingleRelations() as $relation) {
            $modelName = (new \ReflectionClass($relation['relation']->getModel()))->getShortName();
            $this->imports[] = 'import ' . $modelName . ' from "./' . $modelName . '"';

            $this->properties[$relation['method']->name] = '';
        }
    }

    protected function prepareDestruction(): string
    {
        $str = '';
        foreach ($this->properties as $key => $value) {
            $str .= ! empty($value) ? $key . '="' . $value . '",' : $key . ',';
        }

        return $str;
    }

    protected function preparePropertyAssignment(): string
    {
        $str = '';
        foreach ($this->properties as $key => $value) {
            $str .= "\t\t" . 'this.' . $key . '=' . $key . ';' . PHP_EOL;
        }

        return $str;
    }

    protected function preparePropertySetters(): string
    {
        $str = '';
        foreach ($this->modelRelationReader->getSingleRelations() as $relation) {
            $methodName = $relation['method']->name;
            $modelName = (new \ReflectionClass($relation['relation']->getModel()))->getShortName();
            $this->imports[] = 'import ' . $modelName . ' from "./' . $modelName . '"';
            $str .= <<<END
       set $methodName(data) {
            this.$methodName= new $modelName(data)
    }
\n
END;
        }

        return $str;
    }

    public function save()
    {
        $fullPath = config('laracrud.reactjs.rootPath') . '/models/' . $this->shortName . '.js';
        $migrationFile = new \SplFileObject($fullPath, 'w+');
        $migrationFile->fwrite($this->template());
    }
}
