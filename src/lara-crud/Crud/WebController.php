<?php

namespace LaraCrud\Crud;

use Illuminate\Database\Eloquent\Model;
use LaraCrud\Repositories\ControllerRepository;

class WebController
{
    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * @var array
     */
    protected $methods = [];

    /**
     * WebController constructor.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->repository = new ControllerRepository($model);
    }
}
