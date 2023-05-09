<?php

namespace LaraCrud\Repositories;

use Illuminate\Database\Eloquent\Model;

class ReactJsApiServiceRepository extends AbstractControllerRepository
{
    /**
     * Controller Full Namespace.
     *
     * @var string
     */
    protected string $controller;

    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    private Model $model;

    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $parentModel = '';

    public function __construct(string $controller, Model $model, ?Model $parentModel = null)
    {
        $this->model = $model;

        $this->controller = $controller;
        $this->parentModel = $parentModel;
        $this->addMethods($controller);
    }

    public function addMethods()
    {
    }
}
