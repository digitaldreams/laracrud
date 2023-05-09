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

    protected function formRules(ReactJsFormInputBuilder $reactJsFormInputBuilder)
    {
        $rules = [];
        if ($reactJsFormInputBuilder->required) {
            $rules['required'] = true;
        }

        if ($reactJsFormInputBuilder->min > 0) {
            $key = 'text' == $reactJsFormInputBuilder->type ? 'minlength' : 'min';
            $rules[$key] = $reactJsFormInputBuilder->min;
        }

        if ($reactJsFormInputBuilder->max > 0) {
            $key = 'text' == $reactJsFormInputBuilder->type ? 'maxlength' : 'max';

            $rules[$key] = $reactJsFormInputBuilder->max;
        }

        if (in_array($reactJsFormInputBuilder->type, ['radio', 'select'])) {
            $rules['in'] = $reactJsFormInputBuilder->options;
        }
        if ('number' == $reactJsFormInputBuilder->type) {
            $rules['numeric'] = true;
        }
        if ('url' == $reactJsFormInputBuilder->type) {
            $rules['url'] = true;
        }
        if ('email' == $reactJsFormInputBuilder->type) {
            $rules['email'] = true;
        }
        if ('file' == $reactJsFormInputBuilder->type) {
            $rules['mimes'] = [];
        }

        return $rules;
    }

    public function fieldTemplate($column, $inputBuilder)
    {
    }

    protected function getValidationRules(): array
    {
        $controllerReader = new ControllerReader($this->controller::class);
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
