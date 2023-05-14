<?php

namespace LaraCrud\Generators\ReactJs;

use Illuminate\Routing\Route;
use LaraCrud\Contracts\Crud;
use LaraCrud\Helpers\TemplateManager;
use LaraCrud\Services\ControllerReader;

class ReactJsApiEndpointCrud implements Crud
{
    /**
     * @var \LaraCrud\Services\ControllerReader
     */
    private readonly ControllerReader $controllerReader;

    protected string $shortName;

    protected $codes = [];

    /**
     * ReactJsServiceCrud constructor.
     *
     *
     * @throws \ReflectionException
     */
    public function __construct(string $controller)
    {
        $this->controllerReader = new ControllerReader($controller);
        $this->shortName = str_replace('Controller', '', (new \ReflectionClass($controller))->getShortName());
        $this->process();
    }

    protected function process()
    {
        $methods = $this->controllerReader->getMethods();
        $routes = $this->controllerReader->getRoutes();
        foreach ($methods as $method => $reflectionMethod) {
            $this->codes[] = $this->prepareMethod($reflectionMethod, $routes[$method]);
        }
    }

    public function template()
    {
        return (new TemplateManager('reactjs/apiEndpoint.txt', [
            'methods' => implode("\n", $this->codes),
        ]))->get();
    }

    public function save()
    {
        $fullPath = config('laracrud.reactjs.rootPath') . '/apiEndpoints/' . $this->shortName . 'ApiEndpoint.js';
        $migrationFile = new \SplFileObject($fullPath, 'w+');
        $migrationFile->fwrite($this->template());
    }

    protected function prepareMethod(\ReflectionMethod $reflectionMethod, Route $route)
    {
        $uri = str_replace('{', '${', $route->uri);
        $params = $this->routeParam($route);
        $body = <<<END
    {$reflectionMethod->name}($params) {
        return `$uri`;
    },
END;

        return $body;
    }

    /**
     * @param $route
     */
    protected function routeParam($route): string
    {
        $params = '';
        if ($route->parameterNames()) {
            foreach ($route->parameterNames() as $param) {
                $params .= $param . ',';
            }
        }
        $params = rtrim($params, ',');

        return $params;
    }
}
