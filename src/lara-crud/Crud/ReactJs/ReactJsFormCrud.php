<?php

namespace LaraCrud\Crud\ReactJs;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use LaraCrud\Contracts\Crud;
use LaraCrud\Helpers\TemplateManager;

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
        $this->componentName = (new \ReflectionClass($this->model))->getShortName().'Component';

    }

    public function template()
    {
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
}
