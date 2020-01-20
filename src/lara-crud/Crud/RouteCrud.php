<?php

namespace LaraCrud\Crud;

use Illuminate\Support\Facades\Route;
use LaraCrud\Contracts\Crud;
use LaraCrud\Helpers\Helper;
use LaraCrud\Helpers\TemplateManager;
use Illuminate\Support\Str;
/**
 * Create Routes based on controller method and its parameters
 * We will use ReflectionClass to inspect Controller and its method to generate routes based on it
 *
 * @author Tuhin
 */
class RouteCrud implements Crud
{
    use Helper;
    /**
     * Save all register routes name. To avoid name conficts for new routes
     * @var array
     */
    public $routesName = [];

    /**
     * Save all methods name group by controller name which have route defined.
     *
     * @var array
     */
    public $methodNames = [];

    /**
     * All registerd Laravel Routes will be stored here.
     *
     * @var array
     * here is a single route example
     * [
     * 'name'=>'get.user.profile',
     * 'path'=>'',
     * 'controller'=>UserController,
     * 'action'=>'profile',
     * 'method'=>'GET'
     * ]
     */
    public $routes = [];

    /**
     * List of available controllers
     * @var array
     */
    public $controllers = [];

    /**
     * Save all methods name group by controller name
     * To check which method have no route defined yet by comparing to $methodNames
     *
     * @var array
     */
    public $controllerMethods = [];

    /**
     * It is possible to have Controller under Admin folder with the namespae of Admin.
     * @var string
     */
    public $subNameSpace = '';

    public $errors = [];

    protected $api = false;

    protected $template = 'web';

    protected $namespace;

    public function __construct($controller = '', $api = false)
    {
        if (!is_array($controller)) {
            $this->controllers[] = $controller;
        } else {
            $this->controllers = $controller;
        }
        $this->api = $api;

        $this->getRoute();
        $this->fetchControllerMethods();

        $this->template = !empty($api) ? 'api' : 'web';
        $this->namespace = $api == true ? config('laracrud.controller.apiNamespace') : config('laracrud.controller.namespace');
        $this->namespace = rtrim($this->getFullNS($this->namespace), "\\") . "\\";


    }


    /**
     * This will get all defined routes.
     */
    public function getRoute()
    {
        if ($this->api) {
            $routes = [];
            $routeCollection = \Dingo\Api\Facade\Route::getRoutes();
            foreach ($routeCollection as $rts) {
                foreach ($rts as $route) {
                    $routes[] = $route;
                }
            }
        } else {
            $routes = Route::getRoutes();
        }

        foreach ($routes as $route) {
            $controllerName = strstr($route->getActionName(), '@', true);
            $methodName = str_replace("@", "", strstr($route->getActionName(), '@'));
            $this->routes[$route->getActionName()] = [
                'name' => $route->getName(),
                'path' => $route->uri(),
                'controller' => $controllerName,
                'method' => $methodName,
                'http_verbs' => $route->methods(),
                'action' => $route->getActionName(),
                'parameters' => $route->parameterNames()
            ];

            if (!empty($controllerName)) {
                $this->methodNames[$controllerName][] = $methodName;
            }

            if (!empty($route->getName())) {
                $this->routesName[] = $route->getName();
            }
        }
    }

    /**
     * Get all controller methods which is public
     */
    public function fetchControllerMethods()
    {
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

    /**
     * Child class all the method of its parent. But we will accept only child class method.
     *
     * @param string $controllerName
     * @param string $reflectionMethods
     * @return array
     */
    protected function filterMethod($controllerName, $reflectionMethods)
    {
        $retMethods = [];
        foreach ($reflectionMethods as $method) {
            if (substr_compare($method->name, '__', 0, 2) != 0 && $method->class == $controllerName) {
                $retMethods[] = $method->name;
            }
        }
        return $retMethods;
    }

    /**
     * Append route to routes.php file
     * @param string $routesCode
     */
    public function appendRoutes($routesCode)
    {
        $file = $this->getRouteFileName();
        $routePath = file_exists($file) ? $file : base_path($file);
        if (file_exists($routePath)) {
            $splFile = new \SplFileObject($routePath, 'a');
            $splFile->fwrite($routesCode);
        }
    }

    /**
     * Get route file name based on web or api
     *
     * @return string
     */
    protected function getRouteFileName()
    {
        return $this->api == true ? config('laracrud.route.api') : config('laracrud.route.web');
    }

    /**
     * Generate an idividual routes
     *
     * @param string $controllerName e.g. UserController
     * @param string $method e.g. GET,PUT,POST,DELETE based on the prefix of method name.
     * If a controller method name is postSave then its method will be post
     * @param string $fullClassName
     * @param string $subNameSpace
     * @return string
     */
    public function generateRoute($controllerName, $method, $fullClassName = '', $subNameSpace = '')
    {
        $matches = [];
        $path = '';
        $routeName = '';
        preg_match('/^(get|post|put|delete)[A-Z]{1}/', $method, $matches);

        $routeMethodName = 'get';

        if (!empty($subNameSpace)) {
            $routeName = strtolower($subNameSpace) . ".";
        }

        $path .= strtolower($method);
        if (count($matches) > 0) {
            $routeMethodName = array_pop($matches);
            $path = substr_replace($path, '', 0, strlen($routeMethodName));
        }

        $path .= $this->addParams($fullClassName, $method);

        $controllerShortName = str_replace("Controller", "", $controllerName);


        $actionName = $controllerName . '@' . $method;
        $routeName .= Str::plural(strtolower($controllerShortName)) . '.' . strtolower($method);

        $tempObj = new TemplateManager('route/' . $this->template . '/template.txt', [
            'method' => $routeMethodName,
            'path' => $path,
            'routeName' => $routeName,
            'action' => $actionName
        ]);
        return $tempObj->get();
    }

    /**
     * One method may have several params are some may have default values and some may not have.
     * we will inspect this params and define in routes respectively
     *
     * @param string $controller
     * @param string $method
     * @return string
     */
    public function addParams($controller, $method)
    {
        $params = '';
        $reflectionMethod = new \ReflectionMethod($controller, $method);

        foreach ($reflectionMethod->getParameters() as $param) {
            // print_r(get_class_methods($param));
            if ($param->getClass()) {
                continue;
            }
            $optional = $param->isOptional() == TRUE ? '?' : "";
            $params .= '/{' . $param->getName() . $optional . '}';
        }
        return $params;
    }

    /**
     * Process template and return complete code
     * @return mixed
     */
    public function template()
    {
        $retRoutes = '';
        $resourceMethods = ['index', 'show', 'store', 'update', 'destroy'];

        if (empty($this->api)) {
            $resourceMethods = array_merge($resourceMethods, ['create', 'edit']);
        }

        foreach ($this->controllerMethods as $controllerName => $ctr) {
            $controllerRoutes = '';
            $subNameSpace = '';
            $resourceRTemp = '';

            $path = str_replace([$this->namespace, $ctr['shortName']], "", $ctr['full_name']);
            $path = trim($path, "\\");
            $controllerShortName = strtolower(str_replace("Controller", "", $ctr['shortName']));

            if (!empty($path)) {
                $subNameSpace = ',' . "'namespace'=>'" . $path . "'";
                $controllerShortName = strtolower($path) . "/" . $controllerShortName;
            }

            $routesMethods = isset($this->methodNames[$controllerName]) ? $this->methodNames[$controllerName] : [];
            $controllerMethods = isset($ctr['methods']) ? $ctr['methods'] : [];
            $newRouteMethods = array_diff($controllerMethods, $routesMethods);

            $resources = array_intersect($resourceMethods, $newRouteMethods);

            if (count($resourceMethods) == count($resources)) {
                $newRouteMethods = array_diff($newRouteMethods, $resources);
                $tableName = Str::plural(strtolower(str_replace("Controller", "", $ctr['shortName'])));
                $resourceRTempObj = new TemplateManager('route/' . $this->template . '/resource.txt', [
                    'table' => $tableName,
                    'controller' => $ctr['shortName']
                ]);
                $resourceRTemp = $resourceRTempObj->get();
            }

            foreach ($newRouteMethods as $newMethod) {
                $controllerRoutes .= $this->generateRoute($ctr['shortName'], $newMethod, $controllerName, $path);
            }

            if (empty($controllerRoutes)) {
                $retRoutes .= $resourceRTemp;
                continue;
            }

            $routeGroupTempObj = new TemplateManager('route/' . $this->template . '/group.txt', [
                'namespace' => $subNameSpace,
                'routes' => $controllerRoutes,
                'prefix' => Str::plural($controllerShortName)
            ]);
            $routeGroupTemp = $routeGroupTempObj->get();
            $retRoutes .= $routeGroupTemp;
            $retRoutes .= $resourceRTemp;
        }
        return $retRoutes;
    }

    /**
     * Get code and save to disk
     * @return mixed
     */
    public function save()
    {
        $routesCode = $this->template();
        $this->appendRoutes($routesCode);
    }
}