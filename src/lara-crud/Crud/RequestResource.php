<?php
/**
 * Tuhin Bepari <digitaldreams40@gmail.com>
 */
namespace LaraCrud\Crud;


use LaraCrud\Contracts\Crud;
use LaraCrud\Helpers\Helper;
use LaraCrud\Helpers\TemplateManager;

class RequestResource implements Crud
{
    use Helper;

    /**
     * @var
     */
    protected $table;
    /**
     * Request Class parent Namespace.
     * @var string
     */
    protected $namespace;

    /**
     * @var array|string
     */

    protected $methods = ['index', 'show', 'create', 'store', 'update', 'destroy'];

    /**
     * RequestControllerCrud constructor.
     * @param $table
     * @param string $only
     * @internal param string $controller
     */

    public function __construct($table, $only = '')
    {
        $this->table = $table;

        if (!empty($only) && is_array($only)) {
            $this->methods = $only;
        }
        $this->namespace = trim(config('laracrud.request.namespace'), "/") . '\\' . ucfirst($table);
        $this->modelName = $this->getModelName($table);
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
        $publicMethods = $this->methods;
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