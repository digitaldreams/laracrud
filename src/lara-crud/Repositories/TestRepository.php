<?php


namespace LaraCrud\Repositories;

use Illuminate\Database\Eloquent\Model;
use LaraCrud\Builder\Test\ControllerReader;
use LaraCrud\Builder\Test\Methods\ControllerMethod;
use LaraCrud\Configuration;
use LaraCrud\Helpers\Helper;

class TestRepository extends AbstractControllerRepository
{
    use Helper;

    /**
     * Controller Full Namesapce.
     *
     * @var string
     */
    protected string $controller;
    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    private Model $model;

    public function __construct(string $controller, Model $model, bool $isApi = false)
    {
        if (!class_exists($controller)) {
            $ns = $isApi == true ? config('laracrud.controller.apiNamespace') : config('laracrud.controller.namespace');
            $fullNs = $this->getFullNS($ns);
            $controller = $fullNs . '\\' . $controller;
        }

        if (!class_exists($controller)) {
            throw new \Exception(sprintf('Unable to find %s', $controller));
        }
        $this->controller = $controller;
        $this->isApi = $isApi;

        $this->addMethods($controller);
        $this->model = $model;
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
            $this->addMethod($method);
        }
        return $this;
    }

}
