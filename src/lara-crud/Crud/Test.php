<?php

namespace LaraCrud\Crud;

use LaraCrud\Helpers\TestMethod;

/**
 * Create Routes based on controller method and its parameters
 * We will use ReflectionClass to inspect Controller and its method to generate routes based on it
 *
 * @author Tuhin
 */
class Test extends RouteCrud
{
    protected $params = [];

    /**
     * @var
     */
    protected $suffix;
    /**
     * @var
     */
    protected $path;


    public function __construct($controller = '', $api = false)
    {
        $this->suffix = config('laracrud.test.feature.suffix', 'Test');
        $this->path = rtrim(base_path(config('laracrud.test.feature.path', 'tests/Feature')), "/");

        parent::__construct($controller, $api);
    }

    /**
     * @param $controller
     * @param $method
     * @return bool
     */
    public function hasRoute($controller, $method)
    {
        return (isset($this->methodNames[$controller]) && in_array($this->methodNames[$controller], $method));
    }

    /**
     * Process template and return complete code
     * @return mixed
     */
    public function template()
    {
        $testCodes = '';

        foreach ($this->controllerMethods as $controllerName => $ctr) {
            $testCode = '';
            foreach ($ctr['methods'] as $method) {

                if ($this->hasRoute($ctr['full_name'], $method)) {
                    $routeInfo = $this->getRouteInfo();
                    if (!empty($routeInfo)) {
                        $testCode .= (new TestMethod($routeInfo))->template();
                    }
                }
            }
            $testCodes[$ctr['shortName']] = $testCode;
        }
        return $testCodes;
    }

    protected function getRouteInfo($controller, $method)
    {
        $action = $controller . '@' . $method;
        return isset($this->routes[$action]) ? $this->routes[$action] : [];
    }

    /**
     * Get code and save to disk
     * @return mixed
     * @throws \Exception
     */
    public function save()
    {
        $testCodes = $this->template();
        foreach ($testCodes as $name => $testCode) {

            $this->saveFile($name, $testCode);
        }
    }

    /**
     * @param $fileName
     * @param $content
     * @throws \Exception
     */
    public function saveFile($fileName, $content)
    {
        $fileName = $this->path . '/' . $fileName . $this->suffix . '.php';
        if (file_exists($fileName)) {
            throw new \Exception('TestClass already exists');
        }
        $testClass = new \SplFileObject($fileName, 'w+');
        $testClass->fwrite($content);
    }
}