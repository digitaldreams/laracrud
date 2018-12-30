<?php
/**
 * Tuhin Bepari <digitaldreams40@gmail.com>
 */

namespace LaraCrud\Crud;


use LaraCrud\Contracts\Crud;
use LaraCrud\Helpers\Helper;
use LaraCrud\Helpers\TemplateManager;
use Illuminate\Support\Facades\Gate;

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
     * Name of the folder where Request Classes will be saved.
     * @var string
     */
    protected $folderName = '';

    /**
     * @var array|string
     */

    protected $methods = ['index', 'show', 'create', 'store', 'edit', 'update', 'destroy'];

    protected $template = '';

    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * @var
     */
    protected $policy;

    /**
     * RequestControllerCrud constructor.
     * @param $table
     * @param string $only
     * @param bool $api
     * @param string $name
     * @internal param string $controller
     */

    public function __construct($table, $only = '', $api = false, $name = '')
    {
        $this->table = $table;
        $this->folderName = !empty($name) ? $name : $this->table;

        if (!empty($only) && is_array($only)) {
            $this->methods = $only;
        }
        $ns = !empty($api) ? config('laracrud.request.apiNamespace') : config('laracrud.request.namespace');
        $this->namespace = $this->getFullNS(trim($ns, "/")) . '\\' . ucfirst(camel_case($this->folderName));
        $this->modelName = $this->getModelName($table);
        $this->template = !empty($api) ? 'api' : 'web';
    }

    /**
     * Process template and return complete code
     * @param string $authorization
     * @return mixed
     */
    public function template($authorization = 'true')
    {
        $tempMan = new TemplateManager('request/' . $this->template . '/template.txt', [
            'namespace' => $this->namespace,
            'requestClassName' => $this->modelName,
            'authorization' => $authorization,
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
        $publicMethods = $this->methods;
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
                    $requestStore = new Request($this->table, ucfirst(camel_case($this->folderName)) . '/Store', $isApi);
                    $requestStore->setAuthorization($this->getAuthCode('create'));
                    $requestStore->save();
                } elseif ($method === 'update') {
                    $requestUpdate = new Request($this->table, ucfirst(camel_case($this->folderName)) . '/Update', $isApi);
                    $requestUpdate->setAuthorization($this->getAuthCode('update'));
                    $requestUpdate->save();
                } else {
                    $auth = 'true';
                    if ($method === 'edit') {
                        $auth = $this->getAuthCode('update');
                    } elseif ($method === 'show') {
                        $auth = $this->getAuthCode('view');
                    } elseif ($method === 'destroy') {
                        $auth = $this->getAuthCode('delete');
                    } else {
                        $auth = $this->getAuthCode($method);
                    }
                    $model = new \SplFileObject($filePath, 'w+');
                    $model->fwrite($this->template($auth));
                }
            }
        }
    }

    /**
     * @param string $model
     * @return $this
     */
    public function setModel($model = '')
    {
        if (empty($model)) {
            return $this;
        }

        if (!class_exists($model)) {

            $modelNS = $this->getFullNS(config('laracrud.model.namespace'));
            $fullClass = $modelNS . '\\' . $model;

            if (class_exists($fullClass)) {
                $this->model = $fullClass;
            }
        } else {
            $this->model = $model;
        }
        if (class_exists($this->model)) {
            $policies = Gate::policies();
            $this->policy = $policies[$this->model] ?? false;
        }
        return $this;
    }

    private function getAuthCode($methodName)
    {
        $auth = 'true';
        if (class_exists($this->policy) && method_exists($this->policy, $methodName)) {
            if (in_array($methodName, ['create', 'store'])) {
                $code = '\\' . $this->model . '::class)';
            } else {
                $modelName = (new \ReflectionClass($this->model))->getShortName();
                $code = '$this->route(\'' . strtolower($modelName) . '\'))';
            }
            $auth = 'auth()->user()->can(\'' . $methodName . '\', ' . $code;
        }
        return $auth;
    }
}