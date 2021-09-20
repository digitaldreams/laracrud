<?php

namespace LaraCrud\Crud\ReactJs;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use LaraCrud\Contracts\Crud;
use LaraCrud\Contracts\TableContract;
use LaraCrud\Helpers\TemplateManager;
use LaraCrud\Services\ControllerMethodReader;
use LaraCrud\Services\ControllerReader;

class ReactJsFormCrud implements Crud
{
    protected string $componentName;

    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * @var \App\Http\Controllers\Controller
     */
    protected $controller;

    /**
     * ReactJsFormCrud constructor.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param \App\Http\Controllers\Controller    $controller
     */
    public function __construct(Model $model, Controller $controller)
    {
        $this->model = $model;
        $this->controller = $controller;
        $this->componentName = (new \ReflectionClass($this->model))->getShortName() . 'Form';
    }

    public function template()
    {
        print_r($this->getValidationRules());

        return (new TemplateManager('reactjs/form.txt', [
            'componentName' => $this->componentName,
            'validationRules' => '',
            'initialData' => '',
            'fields' => '',
        ]))->get();
    }

    public function save()
    {
        $fullPath = config('laracrud.reactjs.rootPath') . '/components/forms/' . $this->componentName . '.js';
        $migrationFile = new \SplFileObject($fullPath, 'w+');
        $migrationFile->fwrite($this->template());
    }

    protected function generateFields(): string
    {

    }

    protected function getValidationRules(): array
    {
        $controllerReader = new ControllerReader(get_class($this->controller));
        $methods = $controllerReader->getMethods();
        $routes = $controllerReader->getRoutes();

        foreach ($methods as $name => $reflectionMethod) {
            if (in_array($name, ['store', '__invoke'])) {
                $methodReader = new ControllerMethodReader($reflectionMethod, $routes[$name]);

                return $methodReader->getCustomRequestClassRules();
            }
        }
    }

    protected function fetchColumnClasses(): array
    {
        $retColumn = [];
        $tableRepository = app()->make(TableContract::class, ['table' => $this->model->getTable()]);
        foreach ($tableRepository->columns() as $column) {
            $retColumn[$column->name()] = $column;
        }

        return $retColumn;
    }
}
