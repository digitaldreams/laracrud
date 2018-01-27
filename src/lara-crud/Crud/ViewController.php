<?php
/**
 * User: Tuhin
 * Date: 1/25/2018
 * Time: 10:38 PM
 */

namespace LaraCrud\Crud;


use LaraCrud\Helpers\Helper;
use LaraCrud\Helpers\ClassInspector;
use LaraCrud\View\Create;
use LaraCrud\View\Edit;
use LaraCrud\View\Index;
use LaraCrud\View\Show;
use DbReader\Table as TableReader;

class ViewController extends RouteCrud
{
    use Helper;

    /**
     * @var array
     */
    protected $foundViews = [];

    /**
     * @var array
     */
    protected $notFoundViews = [];

    /**
     * @var TableReader
     */
    protected $tableReader;

    /**
     * ViewController constructor.
     * @param $controller
     * @param TableReader $tableReader
     */
    public function __construct($controller, TableReader $tableReader)
    {
        parent::__construct($controller);
        $this->tableReader = $tableReader;
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
                            $classIns = new ClassInspector($controllerFullName);
                            $args = $classIns->prepareMethodArgs($method);
                            $reflectionMethod = new \ReflectionMethod($controllerFullName, $method);
                            $response = $reflectionMethod->invokeArgs(new $controllerFullName, $args);
                            if (is_object($response) && $response instanceof \Illuminate\View\View) {
                                $this->foundViews[$response->getPath()] = $response->getName();
                            }
                        } catch (\InvalidArgumentException $e) {
                            $message = $e->getMessage();
                            if (preg_match("/View\s(.*)\ not found./", $message, $matches)) {
                                if (count($matches) > 1) {
                                    $this->notFoundViews[] = $matches[1];
                                }
                            }
                        } catch (\Exception $e) {
                            continue;
                        }

                    }
                }
            }
        }
    }

    protected function makeFolder($pathArr)
    {

        $currentPath = '';
        $viewPath = config('laracrud.view.path');
        foreach ($pathArr as $path) {
            $currentPath = $currentPath . "/" . $path;
            $folder = rtrim($viewPath) . "/" . $currentPath;
            if (!file_exists($folder)) {
                mkdir($folder);
            }
        }

    }


    public function save()
    {
        foreach ($this->notFoundViews as $view) {
            $view = trim($view, "[]");

            $pathArr = explode(".", $view);
            $viewFileName = array_pop($pathArr);
            $this->makeFolder($pathArr);


        }
    }

    public function makeView($viewFileName, $type = '')
    {
        switch ($viewFileName) {
            case 'create':
                $pageMaker = new Create($this->tableReader, $viewFileName);
                break;
            case 'edit':
                $pageMaker = new Edit($this->tableReader, $viewFileName);
                break;
            case 'show':
                $pageMaker = new Show($this->tableReader, $viewFileName, $type);
                break;
            case 'index':
                $pageMaker = new Index($this->tableReader, $viewFileName, $type);
                break;
            default:
                break;
        }
    }

}