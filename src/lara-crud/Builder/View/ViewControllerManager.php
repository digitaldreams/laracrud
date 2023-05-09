<?php

namespace LaraCrud\Builder\View;

use LaraCrud\Builder\Model;
use LaraCrud\Configuration;
use LaraCrud\Services\ControllerMethodReader;
use LaraCrud\Services\ControllerReader;

class ViewControllerManager
{
    protected ControllerMethodReader $index;

    protected ControllerMethodReader $show;

    protected ControllerMethodReader $create;

    protected ControllerMethodReader $edit;

    protected ControllerMethodReader $store;

    protected ControllerMethodReader $update;

    protected ControllerMethodReader $destroy;

    /**
     * Other Controller methods.
     *
     * @var ControllerMethodReader[]
     */
    protected array $methods;

    protected array $viewFilePaths = [];

    protected array $breadcrumbs = [];

    /**
     * ViewControllerManager constructor.
     */
    public function __construct(private readonly string $controller, private readonly Model $model, private readonly ?\LaraCrud\Builder\Model $parent)
    {
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
