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

    protected array $formRules = [];

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
        $this->generateFields();

        return (new TemplateManager('reactjs/form.txt', [
            'componentName' => $this->componentName,
            'validationRules' => json_encode($this->formRules, JSON_PRETTY_PRINT),
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

    protected function generateFields()
    {
        $validationRules = $this->getValidationRules();
        if (! empty($validationRules)) {
            foreach ($validationRules as $column => $rule) {
                $rule = is_string($rule) ? explode('|', $rule) : $rule;
                $inputBuilder = new ReactJsFormInputBuilder($rule);
                if (true !== $inputBuilder->isArray) {
                    $this->formRules[$column] = $this->formRules($inputBuilder);
                }
            }
        }
    }

    protected function formRules(ReactJsFormInputBuilder $inputBuilder)
    {
        $rules = [];
        if ($inputBuilder->required) {
            $rules['required'] = true;
        }

        if ($inputBuilder->min > 0) {
            $key = 'text' == $inputBuilder->type ? 'minlength' : 'min';
            $rules[$key] = $inputBuilder->min;
        }

        if ($inputBuilder->max > 0) {
            $key = 'text' == $inputBuilder->type ? 'maxlength' : 'max';

            $rules[$key] = $inputBuilder->max;
        }

        if (in_array($inputBuilder->type, ['radio', 'select'])) {
            $rules['in'] = $inputBuilder->options;
        }
        if ('number' == $inputBuilder->type) {
            $rules['numeric'] = true;
        }
        if ('url' == $inputBuilder->type) {
            $rules['url'] = true;
        }
        if ('email' == $inputBuilder->type) {
            $rules['email'] = true;
        }
        if ('file' == $inputBuilder->type) {
            $rules['mimes'] = [];
        }

        return $rules;
    }

    public function fieldTemplate($column, $inputBuilder)
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
