<?php

namespace LaraCrud\Generators;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use LaraCrud\Contracts\FileGeneratorContract;
use LaraCrud\Helpers\Helper;
use LaraCrud\Helpers\NamespaceResolver;
use LaraCrud\Helpers\TemplateManager;

/**
 * Create Routes based on controller method and its parameters
 * We will use ReflectionClass to inspect Controller and its method to generate routes based on it.
 *
 * @author Tuhin
 */
class RouteCrud implements FileGeneratorContract
{
    use Helper;

    /**
     * Save all register routes name. To avoid name conficts for new routes.
     *
     */
    public array $routesName = [];

    /**
     * Save all methods name group by controller name which have route defined.
     *
     */
    public array $methodNames = [];

    /**
     * All registerd Laravel Routes will be stored here.
     *
     * @var array
     *            here is a single route example
     *            [
     *            'name'=>'get.user.profile',
     *            'path'=>'',
     *            'controller'=>UserController,
     *            'action'=>'profile',
     *            'method'=>'GET'
     *            ]
     */
    public array $routes = [];

    /**
     * List of available controllers.
     *
     */
    public string $controller;

    /**
     * Save all methods name group by controller name
     * To check which method have no route defined yet by comparing to $methodNames.
     *
     */
    public array $methods = [];

    /**
     * It is possible to have Controller under Admin folder with the namespae of Admin.
     *
     */
    public string $subNameSpace = '';

    public $errors = [];

    protected string $template = 'web';

    protected string $namespace;
    protected string $controllerShortName;

    protected \ReflectionClass $reflectionClass;

    public function __construct(string $controller, protected bool $api = false)
    {
        $this->controller = $controller;
        $this->reflectionClass = new \ReflectionClass($this->controller);
        $methods = $this->reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);
        $this->controllerShortName = strtolower(
            str_replace('Controller', '', (string)$this->reflectionClass->getShortName())
        );
        $this->methods = $this->filterMethod($this->controller, $methods);
        $this->getRoute();
        $this->namespace = NamespaceResolver::getControllerRoot();
        $this->template = !empty($api) ? 'api' : 'web';
    }


    /**
     * Generate an idividual routes.
     *
     * @param string $controllerName e.g. UserController
     * @param string $method e.g. GET,PUT,POST,DELETE based on the prefix of method name.
     *                               If a controller method name is postSave then its method will be post
     * @param string $fullClassName
     * @param string $subNameSpace
     *
     * @return string
     */
    public function generateRoute(
        string $controllerName,
        string $method,
        string $fullClassName = '',
        string $subNameSpace = ''
    ) {
        $matches = [];
        $path = '';
        $routeName = '';
        preg_match('/^(get|post|put|delete)[A-Z]{1}/', $method, $matches);

        $routeMethodName = 'get';

        if (!empty($subNameSpace)) {
            $routeName = strtolower($subNameSpace) . '.';
        }

        $path .= strtolower($method);
        if (count($matches) > 0) {
            $routeMethodName = array_pop($matches);
            $path = substr_replace($path, '', 0, strlen($routeMethodName));
        }

        $path .= $this->addParams($fullClassName, $method);

        $controllerShortName = str_replace('Controller', '', $controllerName);

        $routeName .= Str::plural(strtolower($controllerShortName)) . '.' . strtolower($method);

        return sprintf(
            "Route::%s('%s', [%s::class, '%s'])->name('%s');" . PHP_EOL,
            $routeMethodName,
            $path,
            $this->controller,
            $method,
            $routeName
        );
    }

    /**
     * Process template and return complete code.
     *
     */
    public function template(): string
    {
        $retRoutes = '';

        $remainingMethods = $this->generateResourceRoutes($retRoutes);

        $this->generateSingleRoute($remainingMethods, $retRoutes);

        return $retRoutes;
    }

    private function generateResourceRoutes(&$retRoutes)
    {
        $resourceMethods = ['index', 'show', 'store', 'update', 'destroy'];

        if ($this->api === false) {
            $resourceMethods = [...$resourceMethods, 'create', 'edit'];
        }

        $routesMethods = array_column($this->routes[$this->controller] ?? [], 'method');
        $newRouteMethods = array_diff($this->methods, $routesMethods);

        $resources = array_intersect($resourceMethods, $newRouteMethods);
        if (count($resources) > 2) {
            if (count($resourceMethods) === count($resources)) {
                $retRoutes = sprintf(
                    "Route::resource('%s',%s::class);" . PHP_EOL,
                    Str::plural($this->controllerShortName),
                    $this->controller
                );

                return true;
            } elseif (count($resources) > 3) {
                $methodStr = '';
                $notIncludedMethods = array_diff($this->methods, $resources);
                foreach ($notIncludedMethods as $method) {
                    $methodStr .= "'" . $method . "',";
                }
                $retRoutes = sprintf(
                    "Route::resource('%s',%s::class)->except([%s]);" . PHP_EOL,
                    Str::plural($this->controllerShortName),
                    $this->controller,
                    $methodStr
                );
            } else {
                $methodStr = '';
                foreach ($resources as $method) {
                    $methodStr .= "'" . $method . "',";
                }
                $retRoutes = sprintf(
                    "Route::resource('%s',%s::class)->only([%s]);" . PHP_EOL,
                    Str::plural($this->controllerShortName),
                    $this->controller,
                    $methodStr
                );
                $notIncludedMethods = array_diff($this->methods, $resources);
            }

            return $notIncludedMethods;
        }

        return $newRouteMethods;
    }

    private function generateSingleRoute($remainingMethods, &$retRoutes)
    {
        foreach ($remainingMethods as $method) {
            if (isset($this->routes[$this->controller]) && array_key_exists(
                    $method,
                    $this->routes[$this->controller]
                )) {
                continue;
            }

            $retRoutes .= $this->generateRoute($this->controllerShortName, $method, $this->controller, '');
        }
    }

    /**
     * Get code and save to disk.
     *
     * @return mixed
     */
    public function save()
    {
        $routesCode = $this->template();
        $this->appendRoutes($routesCode);
    }

    /**
     * Append route to routes.php file.
     *
     * @param string $routesCode
     */
    public function appendRoutes(string $routesCode)
    {
        $file = $this->getRouteFileName();
        $routePath = file_exists($file) ? $file : base_path($file);
        if (file_exists($routePath)) {
            $splFile = new \SplFileObject($routePath, 'a');
            $splFile->fwrite($routesCode);
        }
    }

    /**
     * This will get all defined routes.
     */
    public function getRoute(): void
    {
        $routes = Route::getRoutes();

        foreach ($routes as $route) {
            try {
                if (empty($route->getControllerClass())) {
                    continue;
                }
                $this->routes[$route->getControllerClass()][] = [
                    'name' => $route->getName(),
                    'path' => $route->uri(),
                    'controller' => $route->getControllerClass(),
                    'method' => $route->getActionMethod(),
                    'http_verbs' => $route->methods(),
                    'action' => $route->getActionName(),
                    'parameters' => $route->parameterNames(),
                ];
            } catch (\Exception $e) {
                continue;
            }
        }
    }


    /**
     * Child class all the method of its parent. But we will accept only child class method.
     *
     * @param \ReflectionMethod[] $reflectionMethods
     *
     */
    protected function filterMethod(string $controllerName, $reflectionMethods): array
    {
        $retMethods = [];
        foreach ($reflectionMethods as $reflectionMethod) {
            if (0 != substr_compare(
                    (string)$reflectionMethod->name,
                    '__',
                    0,
                    2
                ) && $reflectionMethod->class == $controllerName) {
                $retMethods[] = $reflectionMethod->name;
            }
        }

        return $retMethods;
    }

    /**
     * Get route file name based on web or api.
     *
     * @return string
     */
    protected function getRouteFileName(): string
    {
        return true === $this->api ? config('laracrud.route.api') : config('laracrud.route.web');
    }

    /**
     * One method may have several params are some may have default values and some may not have.
     * we will inspect this params and define in routes respectively.
     *
     * @param string $controller
     * @param string $method
     *
     * @return string
     * @throws \ReflectionException
     *
     */
    public function addParams(string $controller, string $method) {
        $params = '';
        $reflectionMethod = new \ReflectionMethod($controller, $method);

        foreach ($reflectionMethod->getParameters() as $param) {
            // print_r(get_class_methods($param));
            if ($param->getClass()) {
                continue;
            }
            $optional = true === $param->isOptional() ? '?' : '';
            $params .= '/{' . $param->getName() . $optional . '}';
        }

        return $params;
    }
}
