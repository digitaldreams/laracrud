<?php

namespace LaraCrud\Crud;

use DbReader\Table as TableReader;
use LaraCrud\Helpers\ClassInspector;
use LaraCrud\Helpers\Helper;
use LaraCrud\View\Blank;
use LaraCrud\View\Create;
use LaraCrud\View\Edit;
use LaraCrud\View\Index;
use LaraCrud\View\Show;

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
     *
     * @param $controller
     * @param TableReader $tableReader
     */
    public function __construct($controller, TableReader $tableReader)
    {
        parent::__construct($controller);
        $this->tableReader = $tableReader;
        $this->getViewNames();
    }

    public function getViewNames()
    {
        $resourceMethods = ['index', 'create', 'edit', 'show', 'store', 'update', 'destroy'];
        //Illuminate\View\View
        foreach ($this->controllerMethods as $controllerName => $ctr) {
            $controllerFullName = $ctr['full_name'];
            $routesMethods = $this->methodNames[$controllerName] ?? [];
            foreach ($routesMethods as $routeMethod) {
                $actionName = $controllerFullName . '@' . $routeMethod;
                $routeInfo = $this->routes[$actionName] ?? [];

                if (isset($routeInfo['http_verbs'])) {
                    if ((is_array($routeInfo['http_verbs']) && in_array('GET', $routeInfo['http_verbs']) || 'GET' == $routeInfo['http_verbs'])) {
                        try {
                            $classIns = new ClassInspector($controllerFullName);
                            $args = $classIns->prepareMethodArgs($routeMethod);
                            $reflectionMethod = new \ReflectionMethod($controllerFullName, $routeMethod);
                            $response = $reflectionMethod->invokeArgs(new $controllerFullName(), $args);
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
                        } catch (\Exception) {
                            continue;
                        }
                    }
                }
            }
        }
    }

    /**
     * @param $pathArr
     */
    protected function makeFolder($pathArr)
    {
        $currentPath = '';
        $viewPath = config('laracrud.view.path');
        foreach ($pathArr as $path) {
            $currentPath = $currentPath . '/' . $path;
            $folder = rtrim((string) $viewPath) . '/' . $currentPath;
            if (!file_exists($folder)) {
                mkdir($folder);
            }
        }
    }

    /**
     * @return mixed|void
     */
    public function save()
    {
        foreach ($this->notFoundViews as $notFoundView) {
            try {
                $notFoundView = trim((string) $notFoundView, '[]');

                $pathArr = explode('.', $notFoundView);
                $viewFileName = array_pop($pathArr);
                $this->makeFolder($pathArr);
                $folder = $this->getFullPath(implode('/', $pathArr));
                $fullFilePath = $folder . '/' . $viewFileName . '.blade.php';
                $pageMaker = $this->pageMaker($viewFileName)->setFilePath($fullFilePath);
                $pageMaker->save();
            } catch (\Exception $e) {
                $this->errors[] = $e->getMessage();
            }
        }
    }

    /**
     * @param $view
     *
     * @return string
     */
    protected function getFullPath($view)
    {
        $path = str_replace('.', '/', (string) $view);
        $folder = rtrim((string) config('laracrud.view.path'), '/') . '/' . $path;

        return $folder;
    }

    /**
     * @param $viewFileName
     * @param string $type
     */
    protected function pageMaker($viewFileName, $type = ''): \LaraCrud\View\Blank|\LaraCrud\View\Create|\LaraCrud\View\Edit|\LaraCrud\View\Index|\LaraCrud\View\Show
    {
        $pageMaker = match ($viewFileName) {
            'create' => new Create($this->tableReader, $viewFileName),
            'edit' => new Edit($this->tableReader, $viewFileName),
            'show' => new Show($this->tableReader, $viewFileName, $type),
            'index' => new Index($this->tableReader, $viewFileName, $type),
            default => new Blank($this->tableReader),
        };

        return $pageMaker;
    }
}
