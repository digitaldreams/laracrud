<?php

namespace LaraCrud\Crud;

use DbReader\Table;
use LaraCrud\Contracts\Crud;
use LaraCrud\Helpers\Helper;
use LaraCrud\Helpers\TemplateManager;


class Transformer implements Crud
{
    use Helper;
    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * @var string
     */
    protected $modelName;

    /**
     * parent namespace of the Transformer
     * @var string
     */
    protected $namespace;

    /**
     * @var \ReflectionClass
     */
    protected $reflectionClass;

    /**
     * Transformer constructor.
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param bool $name
     */
    public function __construct(\Illuminate\Database\Eloquent\Model $model, $name = false)
    {
        $this->model = $model;
        $this->name = $name;
        $this->namespace = config('laracrud.transformer.namespace');
        $this->reflectionClass = new \ReflectionClass(get_class($model));
        $this->modelName = !empty($name) ? $name : $this->reflectionClass->getShortName() . config('laracrud.transformer.classSuffix', 'Transformer');
    }

    /**
     * Process template and return complete code
     * @return mixed
     */
    public function template()
    {
        return (new TemplateManager('transformer/template.txt', [
            'namespace' => $this->namespace,
            'modelFullName' => get_class($this->model),
            'model' => $this->reflectionClass->getShortName(),
            'properties' => $this->makeProperties(),
            'modelParam' => lcfirst($this->reflectionClass->getShortName()),
            'className' => $this->modelName,
            'availableInclude' => '',
            'defaultInclude' => ''
        ]))->get();
    }

    /**
     * Get code and save to disk
     * @return mixed
     * @throws \Exception
     */
    public function save()
    {
        $filePath = $this->checkPath();
        if (file_exists($filePath)) {
            throw new \Exception('Transformer already exists');
        }
        $model = new \SplFileObject($filePath, 'w+');
        $model->fwrite($this->template());
    }

    private function makeProperties()
    {
        $retStr = '';
        $modelName=lcfirst($this->reflectionClass->getShortName());
        $table = $this->model->getTable();
        $tableLib = new Table($table);
        $columnClasses = $tableLib->columnClasses();
        foreach ($columnClasses as $columnClass) {
            $retStr .= '"' . $columnClass->name() . '"=>$'.$modelName.'->' . $columnClass->name() . "," . PHP_EOL;
        }
        return $retStr;
    }
}