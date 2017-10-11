<?php
/**
 * Tuhin Bepari <digitaldreams40@gmail.com>
 */
namespace LaraCrud\Crud;


use LaraCrud\Contracts\Crud;
use LaraCrud\Helpers\Helper;
use LaraCrud\Helpers\TemplateManager;
use LaraCrud\Helpers\ClassInspector;

class RequestController implements Crud
{
    use Helper;

    /**
     * @var
     */
    protected $table;
    /**
     * @var string
     */
    protected $controllerNs = '';

    /**
     * @var string
     */
    protected $controllerName;

    /**
     * @var ClassInspector
     */
    protected $classInspector;

    /**
     * Request Class parent Namespace.
     * @var string
     */
    protected $namespace;

    /**
     * @var array
     */
    protected $methods = ['index', 'show', 'create', 'store', 'update', 'destroy'];

    /**
     * RequestControllerCrud constructor.
     * @param $table
     * @param string $controller
     * @throws \Exception
     */

    public function __construct($table, $controller = '')
    {
        $this->controllerNs = config('laracrud.controller.namespace', 'App\Http\Controllers');
        $this->table = $table;
        if (!empty($controller)) {
            if (!class_exists($controller)) {
                $this->controllerName = $this->controllerNs . "\\" . $controller;
            }

            if (!class_exists($this->controllerName)) {
                throw new \Exception('Controller ' . $this->controllerName . ' does not exists');
            }

            $this->classInspector = new ClassInspector($this->controllerName);
            $this->namespace = trim(config('laracrud.request.namespace'), "/") . '\\' . ucfirst($table);
            $this->modelName = $this->getModelName($table);
        }
    }

    /**
     * Process template and return complete code
     * @return mixed
     */
    public function template()
    {
        $tempMan = new TemplateManager('request/template.txt', [
            'namespace' => $this->namespace,
            'requestClassName' => $this->modelName,
            'rules' => implode("\n", [])
        ]);
        return $tempMan->get();
    }

    /**
     * Get code and save to disk
     * @return mixed
     */
    public function save()
    {
        $this->checkPath("");
        $publicMethods = $this->classInspector->publicMethods;
        if (!empty($publicMethods)) {

            foreach ($publicMethods as $method) {
                $folderPath = base_path($this->toPath($this->namespace));
                $this->modelName = $this->getModelName($method);
                $filePath = $folderPath . "/" . $this->modelName . ".php";

                if (file_exists($filePath)) {
                    continue;
                }
                if ($method === 'store') {
                    $requestStore = new Request($this->table, ucfirst($this->table) . '/Store');
                    $requestStore->save();
                } elseif ($method === 'update') {
                    $requestUpdate = new Request($this->table, ucfirst($this->table) . '/Update');
                    $requestUpdate->save();
                } else {
                    $model = new \SplFileObject($filePath, 'w+');
                    $model->fwrite($this->template());
                }

            }
        }
    }
}