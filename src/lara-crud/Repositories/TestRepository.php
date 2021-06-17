<?php

namespace LaraCrud\Repositories;

use Illuminate\Database\Eloquent\Model;
use LaraCrud\Builder\Test\ControllerReader;
use LaraCrud\Builder\Test\Methods\ControllerMethod;
use LaraCrud\Builder\Test\Methods\DefaultMethod;
use LaraCrud\Configuration;
use LaraCrud\Helpers\Helper;

class TestRepository extends AbstractControllerRepository
{
    use Helper;

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

    public function __construct(string $controller, Model $model, ?Model $parentModel = null, bool $isApi = false)
    {
        $this->model = $model;

        $this->controller = $controller;
        $this->isApi = $isApi;
        $this->parentModel = $parentModel;
        $this->addMethods($controller);
    }

    /**
     * @param \LaraCrud\Builder\Test\Methods\ControllerMethod $method
     *
     * @return \LaraCrud\Repositories\TestRepository
     */
    public function addMethod(ControllerMethod $method): self
    {
        $this->methods[] = $method;

        return $this;
    }

    /**
     * @param string $controller
     *
     * @return $this
     * @throws \ReflectionException
     */
    public function addMethods(string $controller): self
    {
        $availableMethods = $this->isApi ? Configuration::$testApiMethods : [];
        $cr = new ControllerReader($controller);
        $methods = $cr->getMethods();
        $routes = $cr->getRoutes();

        $insertAbleMethods = array_intersect_key($availableMethods, $routes);
        foreach ($insertAbleMethods as $key => $methodName) {
            $method = new $methodName($methods[$key], $routes[$key]);
            $method->setModel($this->model);
            if ($this->parentModel) {
                $method->setParent($this->parentModel);
            }
            $this->addMethod($method);
            unset($routes[$key]);
        }

        foreach ($routes as $key => $route) {
            $method = new DefaultMethod($methods[$key], $route);
            $method->setModel($this->model);
            if ($this->parentModel) {
                $method->setParent($this->parentModel);
            }
            $this->addMethod($method->init());
        }

        return $this;
    }

    public function setParentModel(Model $model): TestRepository
    {
        $this->parentModel = $model;

        return $this;
    }
}
