<?php

namespace LaraCrud\Crud;

use DbReader\Table;
use Illuminate\Database\Eloquent\Model;
use LaraCrud\Contracts\Crud;
use LaraCrud\Helpers\FakerColumn;
use LaraCrud\Helpers\Helper;
use LaraCrud\Helpers\TemplateManager;

class ModelFactory implements Crud
{
    use Helper;

    /**
     * @var Model
     */
    protected Model $model;

    /**
     * @var Table
     */
    protected $table;

    protected string $namespace;

    protected string $modelName;

    protected $reflection;

    /**
     * ModelFactory constructor.
     *
     * @param Model $model
     *
     * @throws \Exception
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->table = new Table($this->model->getTable());
        $this->reflection = new \ReflectionClass($this->model);
        $this->setNamespace();
    }

    public function save()
    {
        $path = config('laracrud.factory.path');
        $name = $this->getName();
        if (file_exists($path . '/' . $name)) {
            throw new \Exception($name . ' already exists');
        }
        $factory = new \SplFileObject($this->checkPath(), 'w+');
        $factory->fwrite($this->template());
    }

    /**
     * @return mixed|string
     */
    public function template(): string
    {
        return (new TemplateManager('factory/template.txt', [
            'namespace' => $this->namespace,
            'modelClass' => $this->reflection->getName(),
            'modelShortName' => $this->reflection->getShortName(),
            'className' => $this->getName(),
            'columns' => $this->makeColumns(),
        ]))->get();
    }

    /**
     * @return string
     */
    protected function makeColumns(): string
    {
        $arr = '';
        $columns = $this->table->columnClasses();
        foreach ($columns as $column) {
            if ($column->isProtected()) {
                continue;
            }
            $fakerColumn = new FakerColumn($column);
            $default = $fakerColumn->default();
            $columnValue = ! empty($default) ? $default . ',' : '\'\',';
            $arr .= "\t\t\t" . '"' . $column->name() . '" => ' . $columnValue . PHP_EOL;
        }

        return $arr;
    }

    /**
     * @return string
     * @throws \ReflectionException
     *
     */
    protected function getName(): string
    {
        $suffix = config('laracrud.factory.suffix', 'Factory');
        $shortModelName = $this->reflection->getShortName();

        return $this->modelName = $shortModelName . $suffix;
    }

    protected function setNamespace()
    {
        $this->namespace = config('laracrud.factory.namespace');
        $classRootNs = trim(str_replace($this->reflection->getShortName(), "", $this->reflection->getName()), "\\");
        $modelRootNs = $this->getFullNS(config('laracrud.model.namespace', 'Models'));
        $sub = str_replace($modelRootNs, "", $classRootNs);
        if (! empty($sub)) {
            $this->namespace .= "\\" . trim($sub, "\\");
        }
    }
}
