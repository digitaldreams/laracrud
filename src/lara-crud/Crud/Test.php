<?php

namespace LaraCrud\Crud;

use LaraCrud\Contracts\Crud;
use LaraCrud\Helpers\Helper;
use LaraCrud\Helpers\TemplateManager;
use LaraCrud\Helpers\TestMethod;

/**
 * Create Routes based on controller method and its parameters
 * We will use ReflectionClass to inspect Controller and its method to generate routes based on it
 *
 * @author Tuhin
 */
class Test extends RouteCrud implements Crud
{
    use Helper;
    protected $params = [];

    /**
     * @var
     */
    protected $suffix;

    /**
     * @var
     */
    protected $namespace;

    protected $controllerInfo;
    protected $fileName;


    public function __construct($controller = '', $api = false)
    {
        parent::__construct($controller, $api);

        $this->suffix = config('laracrud.test.feature.suffix', 'Test');
        $this->namespace = config('laracrud.test.feature.namespace', 'Tests\Feature');
        $this->controllerInfo = array_shift($this->controllerMethods);
        $this->fileName = $this->controllerInfo['shortName'] . $this->suffix;
    }

    /**
     * @param $controller
     * @param $method
     * @return bool
     */
    public function hasRoute($controller, $method)
    {
        return (isset($this->methodNames[$controller]) && in_array($method, $this->methodNames[$controller]));
    }

    /**
     * Process template and return complete code
     * @return mixed
     */
    public function template()
    {
        return (new TemplateManager('test/' . $this->template . '/template.txt', [
            'namespace' => $this->namespace,
            'import' => '',
            'className' => $this->fileName,
            'methods' => $this->getMethodTestCode()
        ]))->get();
    }

    public function getMethodTestCode()
    {
        $testCodes = '';

        foreach ($this->controllerInfo['methods'] as $method) {
            if ($this->hasRoute($this->controllerInfo['full_name'], $method)) {
                $routeInfo = $this->getRouteInfo($this->controllerInfo['full_name'], $method);
                if (!empty($routeInfo)) {
                    $testCodes .= (new TestMethod($routeInfo, $this->api))->template();
                }
            }
        }
        return $testCodes;
    }

    /**
     * @param $controller
     * @param $method
     * @return array
     */
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
        $fullPath = $this->toPath($this->namespace . "\\" . $this->fileName) . ".php";
        if (file_exists($fullPath)) {
            throw new \Exception('TestClass already exists');
        }
        $testClass = new \SplFileObject($fullPath, 'w+');
        $testClass->fwrite($this->template());
    }

    /**
     * @param $fileName
     * @param $content
     * @throws \Exception
     */
    public function saveFile($fileName, $content)
    {
        $fileName = $this->namespace . '/' . $fileName . $this->suffix . '.php';
    }
}
