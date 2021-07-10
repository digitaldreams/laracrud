<?php

namespace LaraCrud\Builder\View;

use LaraCrud\Builder\Model;
use LaraCrud\Configuration;
use LaraCrud\Services\ControllerMethodReader;
use LaraCrud\Services\ControllerReader;

class ViewControllerManager
{
    private string $controller;

    /**
     * @var \LaraCrud\Builder\Model
     */
    private Model $model;

    /**
     * @var \LaraCrud\Builder\Model|null
     */
    private ?Model $parent;

    protected ControllerMethodReader $index;

    protected ControllerMethodReader $show;

    protected ControllerMethodReader $create;

    protected ControllerMethodReader $edit;

    protected ControllerMethodReader $store;

    protected ControllerMethodReader $update;

    protected ControllerMethodReader $destroy;

    /**
     * ViewControllerManager constructor.
     *
     * @param string                       $controller
     * @param \LaraCrud\Builder\Model      $model
     * @param \LaraCrud\Builder\Model|null $parent
     */
    public function __construct(string $controller, Model $model, ?Model $parent)
    {
        $this->controller = $controller;
        $this->model = $model;
        $this->parent = $parent;
    }

    protected function initMethods($controller)
    {
        $cr = new ControllerReader($controller);
        $methods = $cr->getMethods();
        $routes = $cr->getRoutes();
        foreach (Configuration::$controllerWebMethods as $key => $methodName) {
            if (isset($methods[$key]) && isset($routes[$key])) {
                $method = new $methodName($methods[$key], $routes[$key]);
                $method->setModel($this->model);
                if ($this->parent) {
                    $method->setParent($this->parent);
                }
                if (property_exists($this, $key)) {
                    $this->{$key} = $method;
                }
            }
        }
    }
}
