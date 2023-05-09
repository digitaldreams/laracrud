<?php

namespace LaraCrud\Crud\ReactJs;

use Illuminate\Routing\Route;
use LaraCrud\Contracts\Crud;
use LaraCrud\Helpers\TemplateManager;
use LaraCrud\Services\ControllerMethodReader;

class ReactJsServiceCrud extends ReactJsApiEndpointCrud implements Crud
{
    protected array $imports = [];

    public function __construct(string $controller)
    {
        parent::__construct($controller);
        $apiEndpoint = $this->shortName . 'ApiEndpoint';
        $this->imports[] = 'import Axios from "../../main/config/customAxios"';
        $this->imports[] = 'import ' . $apiEndpoint . ' from "../apiEndpoints/' . $apiEndpoint . '"';
    }

    public function template()
    {
        return (new TemplateManager('reactjs/service.txt', [
            'methods' => implode("\n", $this->codes),
            'import' => implode("\n", $this->imports),
        ]))->get();
    }

    public function save()
    {
        $fullPath = config('laracrud.reactjs.rootPath') . '/services/' . $this->shortName . 'Service.js';
        $migrationFile = new \SplFileObject($fullPath, 'w+');
        $migrationFile->fwrite($this->template());
    }

    protected function prepareMethod(\ReflectionMethod $reflectionMethod, Route $route)
    {
        $apiEndpoint = $this->shortName . 'ApiEndpoint';
        $methodReader = new ControllerMethodReader($reflectionMethod, $route);
        $rules = $methodReader->getCustomRequestClassRules();

        $axiosMethod = strtolower((string) array_shift($route->methods));
        $methodParams = $params = $this->routeParam($route);
        $extraParams = '';
        if (! empty($rules)) {
            $methodParams .= ! empty($methodParams) ? ',data' : 'data';
            $extraParams .= ',data';
        }
        $str = <<<EOD
        $reflectionMethod->name($methodParams){
        return  Axios.$axiosMethod($apiEndpoint.$reflectionMethod->name($params)$extraParams)
        },

EOD;

        return $str;
    }
}
