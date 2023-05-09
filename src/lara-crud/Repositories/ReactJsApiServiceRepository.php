<?php

namespace LaraCrud\Repositories;

use Illuminate\Database\Eloquent\Model;

class ReactJsApiServiceRepository extends AbstractControllerRepository
{
    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $parentModel = '';

    public function __construct(/**
     * Controller Full Namespace.
     */
    protected string $controller, private readonly Model $model, ?Model $parentModel = null)
    {
        $this->parentModel = $parentModel;
        $this->addMethods();
    }

    public function addMethods()
    {
    }
}
