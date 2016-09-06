<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace LaraCrud;

use Illuminate\Support\Facades\Route;

/**
 * Description of RouteCrud
 *
 * @author Tuhin
 */
class RouteCrud extends LaraCrud {

    public $routesName = [];
    public $methodNames = [];
    public $routes = [];
    public $controllers = [];
    public $controllerMethods = [];
    public $subNameSpace = '';

    const PARENT_NAMESPACE = 'App\Http\Controllers\\';

    public function __construct($controller = '') {
        if (!is_array($controller)) {
            $this->controllers[] = $controller;
        } else {
            $this->controllers = $controller;
        }
        $this->getRoute();
        $this->fetchControllerMethods();
    }

    /**
     * This will get all defined routes. 
     */
    public function getRoute() {
        $routes = Route::getRoutes();
        foreach ($routes as $route) {
            $controllerName = strstr($route->getActionName(), '@', true);
            $methodName = str_replace("@", "", strstr($route->getActionName(), '@'));
            $this->routes[] = [
                'name' => $route->getName(),
                'path' => $route->getPath(),
                'controller' => $controllerName,
                'action' => $route->getActionName(),
                'method' => $methodName
            ];

            if (!empty($controllerName)) {
                $this->methodNames[$controllerName][] = $methodName;
            }

            if (!empty($route->getName())) {
                $this->routesName[] = $route->getName();
            }
        }
    }

    public function fetchControllerMethods() {
        foreach ($this->controllers as $controller) {
            $reflectionClass = new \ReflectionClass($controller);
            $methods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);

            $this->controllerMethods[$controller] = array(
                'full_name' => $controller,
                'shortName' => $reflectionClass->getShortName(),
                'description' => $reflectionClass->getDocComment(),
                'methods' => $this->filterMethod($controller, $methods)
            );
        }
    }

    protected function filterMethod($controllerName, $reflectionMethods) {
        $retMethods = [];
        foreach ($reflectionMethods as $method) {
            if (substr_compare($method->name, '__', 0, 2) != 0 && $method->class == $controllerName) {
                $retMethods[] = $method->name;
            }
        }
        return $retMethods;
    }

    public function make() {
        $routesCode = $this->generateContent();
        $this->appendRoutes($routesCode);
    }

    public function appendRoutes($routesCode) {
        $routePath = base_path('/app/Http/routes.php');
        if (file_exists($routePath)) {
            $splFile = new \SplFileObject($routePath, 'a');
            $splFile->fwrite($routesCode);
        }
    }

    public function generateContent() {
        $retRoutes = '';
        foreach ($this->controllerMethods as $controllerName => $ctr) {
            $controllerRoutes = '';
            $subNameSpace = '';

            $path = str_replace([static::PARENT_NAMESPACE, $ctr['shortName']], "", $ctr['full_name']);
            $path = trim($path, "\\");
            $controllerShortName = strtolower(str_replace("Controller", "", $ctr['shortName']));

            if (!empty($path)) {
                $subNameSpace = ',' . "'namespace'=>'" . $path . "'";
                $controllerShortName = strtolower($path) . "/" . $controllerShortName;
            }

            $routesMethods = isset($this->methodNames[$controllerName]) ? $this->methodNames[$controllerName] : [];
            $controllerMethods = isset($ctr['methods']) ? $ctr['methods'] : [];
            $newRouteMethods = array_diff($controllerMethods, $routesMethods);
            foreach ($newRouteMethods as $newMethod) {
                $controllerRoutes.=$this->generateRoute($ctr['shortName'], $newMethod, $controllerName, $path);
            }
            if (empty($controllerRoutes)) {
                continue;
            }



            $reflectionClass = new \ReflectionClass($ctr['full_name']);
            $routeGroupTemp = $this->getTempFile('route_group.txt');
            $routeGroupTemp = str_replace('@@namespace@@', $subNameSpace, $routeGroupTemp);
            $routeGroupTemp = str_replace('@@routes@@', $controllerRoutes, $routeGroupTemp);
            $routeGroupTemp = str_replace('@@prefix@@', $controllerShortName, $routeGroupTemp);
            $retRoutes.=$routeGroupTemp;
        }
        return $retRoutes;
    }

    public function generateRoute($controllerName, $method, $fullClassName = '', $subNameSpace = '') {
        $template = $this->getTempFile('route.txt');
        $matches = [];
        $path = '';
        $routeName = '';
        preg_match('/^(get|post|put|delete)[A-Z]{1}/', $method, $matches);

        $routeMethodName = 'get';

        if (!empty($subNameSpace)) {
            $routeName = strtolower($subNameSpace) . ".";
        }

        $path.=strtolower($method);
        if (count($matches) > 0) {
            $routeMethodName = array_pop($matches);
            $path = substr_replace($path, '', 0, strlen($routeMethodName));
        }

        $path.=$this->addParams($fullClassName, $method);

        $controllerShortName = str_replace("Controller", "", $controllerName);


        $actionName = $controllerName . '@' . $method;
        $routeName.=strtolower($controllerShortName) . '.' . strtolower($method);

        $template = str_replace('@method@', $routeMethodName, $template);
        $template = str_replace('@@path@@', '/' . $path, $template);
        $template = str_replace('@@routeName@@', $routeName, $template);
        $template = str_replace('@@action@@', $actionName, $template);
        return $template;
    }

    public function addParams($controller, $method) {
        $params = '';
        $reflectionMethod = new \ReflectionMethod($controller, $method);

        foreach ($reflectionMethod->getParameters() as $param) {
            // print_r(get_class_methods($param));
            if ($param->getClass()) {
                continue;
            }
            $optional = $param->isOptional() == TRUE ? '?' : "";
            $params.='/{' . $param->getName() . $optional . '}';
        }
        return $params;
    }

}
