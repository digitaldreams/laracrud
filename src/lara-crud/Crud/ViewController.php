<?php
/**
 * User: Tuhin
 * Date: 1/25/2018
 * Time: 10:38 PM
 */

namespace LaraCrud\Crud;


use LaraCrud\Helpers\Helper;
use LaraCrud\Helpers\ClassInspector;
use LaraCrud\Traits\ControllerTrait;

class ViewController extends RouteCrud
{
    use Helper;

    public function __construct($controller)
    {
        parent::__construct($controller);
    }

    public function getViewNames()
    {
        $resourceMethods = ['index', 'create', 'edit', 'show', 'store', 'update', 'destroy'];
        //Illuminate\View\View
        foreach ($this->controllerMethods as $controllerName => $ctr) {
            $controllerFullName = $ctr['full_name'];
            $routesMethods = isset($this->methodNames[$controllerName]) ? $this->methodNames[$controllerName] : [];
            foreach ($routesMethods as $method) {
                $actionName = $controllerFullName . '@' . $method;
                $routeInfo = isset($this->routes[$actionName]) ? $this->routes[$actionName] : [];

                if (isset($routeInfo['http_verbs'])) {
                    if ((is_array($routeInfo['http_verbs']) && in_array('GET', $routeInfo['http_verbs']) || $routeInfo['http_verbs'] == 'GET')) {
                        $this->getViewPath($controllerFullName, $method);
                    }
                }

            }

        }
    }

    /**
     * @param $controller
     * @param $method
     * @return array
     */
    protected function prepareMethodArgs($controller, $method)
    {
        $args = [];
        $reflectionMethod = new \ReflectionMethod($controller, $method);
        foreach ($reflectionMethod->getParameters() as $param) {
            if ($param->getClass()) {
                if (is_subclass_of($param->getClass()->name, \Illuminate\Http\Request::class)) {
                    $requestClass = $param->getClass()->name;
                    $args[] = new $requestClass;
                } elseif (is_subclass_of($param->getClass()->name, \Illuminate\Database\Eloquent\Model::class)) {
                    $modelClass = $param->getClass()->name;
                    $args[] = new $modelClass;
                }
            } else {
                $optional = $param->isOptional() == TRUE ? '?' : "";
                $args[] = '';
            }
        }
        return $args;
    }

    public function save()
    {
    }

    public function template()
    {
    }

}