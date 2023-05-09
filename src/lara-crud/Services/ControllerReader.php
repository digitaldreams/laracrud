<?php

namespace LaraCrud\Services;

use Illuminate\Support\Facades\Route;

class ControllerReader
{
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
     *
     * @throws \ReflectionException
     */
    public function __construct(protected string $controller)
    {
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

    protected function parseRoutes(): array
    {
        $returnRoutes = [];

        $routes = Route::getRoutes();
        foreach ($routes as $route) {
            $controllerName = strstr((string) $route->getActionName(), '@', true);
            $cname = $controllerName ?: $route->getActionName();
            if ($this->controller != $cname) {
                continue;
            }
            $methodName = str_replace('@', '', strstr((string) $route->getActionName(), '@'));
            $methodName = $methodName ?: '__invoke';
            $returnRoutes[$methodName] = $route;
        }

        return $returnRoutes;
    }

    /**
     * Child class all the method of its parent. But we will accept only child class method.
     *
     * @param \ReflectionMethod[] $reflectionMethods
     * @return array
     */
    protected function filterMethod(string $controllerName, array $reflectionMethods)
    {
        $retMethods = [];
        foreach ($reflectionMethods as $reflectionMethod) {
            if (0 != substr_compare($reflectionMethod->name, '__', 0, 2) && $reflectionMethod->class == $controllerName) {
                $retMethods[$reflectionMethod->name] = $reflectionMethod;
            } elseif ($reflectionMethod->name == '__invoke' && $reflectionMethod->class == $controllerName) {
                $retMethods[$reflectionMethod->name] = $reflectionMethod;
            }
        }

        return $retMethods;
    }
}
