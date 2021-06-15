<?php


namespace LaraCrud\Builder\Test;


use Illuminate\Support\Facades\Route;

class ControllerReader
{
    /**
     * controller Full Namespace.
     *
     * @var string
     */
    protected string $controller;
    /**
     * List of Public method's of this controller
     *
     * @var \ReflectionMethod[]
     */
    protected array $methods = [];

    /**
     * @var \Illuminate\Routing\Route[]
     */
    protected array $routes = [];

    /**
     * ControllerReader constructor.
     *
     * @param string $controller
     *
     * @throws \ReflectionException
     */
    public function __construct(string $controller)
    {
        $this->controller = $controller;
        $reflectionClass = new \ReflectionClass($controller);
        $methods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);
        $this->methods = $this->filterMethod($controller, $methods);
        $this->routes = $this->parseRoutes();
    }

    /**
     * @return \ReflectionMethod[]
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * @return \Illuminate\Routing\Route[]
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * @return array
     */
    protected function parseRoutes(): array
    {
        $returnRoutes = [];

        $routes = Route::getRoutes();
        foreach ($routes as $route) {
            $controllerName = strstr($route->getActionName(), '@', true);
            if ($this->controller != $controllerName) {
                continue;
            }
            $methodName = str_replace('@', '', strstr($route->getActionName(), '@'));
            $returnRoutes[$methodName] = $route;
        }

        return $returnRoutes;
    }

    /**
     * Child class all the method of its parent. But we will accept only child class method.
     *
     * @param string              $controllerName
     * @param \ReflectionMethod[] $reflectionMethods
     *
     * @return array
     */
    protected function filterMethod(string $controllerName, array $reflectionMethods)
    {
        $retMethods = [];
        foreach ($reflectionMethods as $method) {
            if (0 != substr_compare($method->name, '__', 0, 2) && $method->class == $controllerName) {
                $retMethods[$method->name] = $method;
            }
        }

        return $retMethods;
    }

}
