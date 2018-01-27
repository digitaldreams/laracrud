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
                        try {
                            $args = $this->prepareMethodArgs($controllerFullName, $method);
                            $reflectionMethod = new \ReflectionMethod($controllerFullName, $method);
                            $response = $reflectionMethod->invokeArgs(new $controllerFullName, $args);
                            if (is_object($response) && $response instanceof \Illuminate\View\View) {
                                print_r($response->getPath()."<br>");
                            }
                        } catch (\Exception $e) {
                            echo 'Exception => ' . $e->getMessage();
                        }
                    }
                }
            }
        }
    }


    public function save()
    {
    }

    public function template()
    {
    }

}