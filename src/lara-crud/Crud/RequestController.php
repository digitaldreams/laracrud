<?php
/**
 * Tuhin Bepari <digitaldreams40@gmail.com>
 */

namespace LaraCrud\Crud;


use LaraCrud\Contracts\Crud;
use LaraCrud\Helpers\ClassInspector;
use LaraCrud\Helpers\Helper;
use LaraCrud\Helpers\TemplateManager;

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

    protected $template;

    /**
     * @var array
     */
    protected $methods = ['index', 'show', 'create', 'store', 'update', 'destroy'];

    /**
     * RequestControllerCrud constructor.
     * @param $table
     * @param string $controller
     * @param bool $api
     * @throws \Exception
     */

    public function __construct($table, $controller = '', $api = false)
    {

        $controllerNs = !empty($api) ? config('laracrud.controller.apiNamespace', 'App\Http\Controllers\Api') : config('laracrud.controller.namespace', 'App\Http\Controllers');
        $this->controllerNs = $this->getFullNS($controllerNs);
        $this->table = $table;
        $this->template = !empty($api) ? 'api' : 'web';
        if (!empty($controller)) {
            if (!class_exists($controller)) {
                $this->controllerName = $this->controllerNs . "\\" . $controller;
            }

            if (!class_exists($this->controllerName)) {
                throw new \Exception('Controller ' . $this->controllerName . ' does not exists');
            }

            $this->classInspector = new ClassInspector($this->controllerName);
            $requestNs = !empty($api) ? config('laracrud.request.apiNamespace') : config('laracrud.request.namespace');
            $this->namespace = $this->getFullNS(trim($requestNs, "/")) . '\\' . ucfirst(camel_case($table));
            $this->modelName = $this->getModelName($table);
        }
    }

    /**
     * Process template and return complete code
     * @return mixed
     */
    public function template()
    {
        $tempMan = new TemplateManager('request/' . $this->template . '/template.txt', [
            'namespace' => $this->namespace,
            'requestClassName' => $this->modelName,
            'rules' => implode("\n", [])
        ]);
        return $tempMan->get();
    }

    /**
     * Get code and save to disk
     * @return mixed
     * @throws \Exception
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
                $isApi = $this->template == 'api' ? true : false;
                if ($method === 'store') {
                    $requestStore = new Request($this->table, ucfirst(camel_case($this->table)) . '/Store', $isApi);
                    $requestStore->save();
                } elseif ($method === 'update') {
                    $requestUpdate = new Request($this->table, ucfirst(camel_case($this->table)) . '/Update', $isApi);
                    $requestUpdate->save();
                } else {
                    $model = new \SplFileObject($filePath, 'w+');
                    $model->fwrite($this->template());
                }

            }
        }
    }
}