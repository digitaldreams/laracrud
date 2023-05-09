<?php

namespace LaraCrud\Crud;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use LaraCrud\Contracts\Crud;
use LaraCrud\Helpers\Helper;
use LaraCrud\Helpers\TemplateManager;

class RequestResource implements Crud
{
    use Helper;

    /**
     * @var string
     */
    protected $table;
    /**
     * Request Class parent Namespace.
     *
     * @var string
     */
    protected $namespace;

    /**
     * Name of the folder where Request Classes will be saved.
     *
     * @var string
     */
    protected $folderName = '';

    /**
     * @var array|string
     */
    protected $methods = ['store', 'update'];

    /**
     * @var string
     */
    protected $template = '';

    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * @var string
     */
    protected $policy;

    /**
     * RequestControllerCrud constructor.
     *
     * @param bool                                $api
     *
     * @internal param string $controller
     */
    public function __construct(\Illuminate\Database\Eloquent\Model $model, ?string $only = '', ?bool $api = false, ?string $name = '')
    {
        $this->table = $model->getTable();
        $this->model = $model;
        $policies = Gate::policies();
        $this->policy = $policies[$this->model::class] ?? false;
        $this->folderName = !empty($name) ? $name : $this->table;

        if (!empty($only) && is_array($only)) {
            $this->methods = $only;
        }
        $ns = !empty($api) ? config('laracrud.request.apiNamespace') : config('laracrud.request.namespace');
        $this->namespace = $this->getFullNS(trim((string) $ns, '/')) . '\\' . ucfirst(Str::camel($this->folderName));
        $this->modelName = $this->getModelName($this->table);
        $this->template = !empty($api) ? 'api' : 'web';
    }

    /**
     * Process template and return complete code.
     *
     * @param string $authorization
     *
     * @return mixed
     */
    public function template($authorization = 'true')
    {
        $tempMan = new TemplateManager('request/' . $this->template . '/template.txt', [
            'namespace' => $this->namespace,
            'requestClassName' => $this->modelName,
            'authorization' => $authorization,
            'rules' => implode("\n", []),
        ]);

        return $tempMan->get();
    }

    /**
     * Get code and save to disk.
     *
     * @return mixed
     * @throws \Exception
     *
     */
    public function save()
    {
        $this->checkPath('');
        $publicMethods = $this->methods;

        if (!empty($publicMethods)) {
            foreach ($publicMethods as $publicMethod) {
                $folderPath = base_path($this->toPath($this->namespace));
                $this->modelName = $this->getModelName($publicMethod);
                $filePath = $folderPath . '/' . $this->modelName . '.php';

                if (file_exists($filePath)) {
                    continue;
                }
                $isApi = 'api' == $this->template ? true : false;

                if ('store' === $publicMethod) {
                    $requestStore = new Request($this->model, ucfirst(Str::camel($this->folderName)) . '/StoreRequest', $isApi);
                    $requestStore->setAuthorization($this->getAuthCode('create'));
                    $requestStore->save();
                } elseif ('update' === $publicMethod) {
                    $requestUpdate = new Request($this->model, ucfirst(Str::camel($this->folderName)) . '/UpdateRequest', $isApi);
                    $requestUpdate->setAuthorization($this->getAuthCode('update'));
                    $requestUpdate->save();
                } else {
                    $auth = 'true';
                    if ('edit' === $publicMethod) {
                        $auth = $this->getAuthCode('update');
                    } elseif ('show' === $publicMethod) {
                        $auth = $this->getAuthCode('view');
                    } elseif ('destroy' === $publicMethod) {
                        $auth = $this->getAuthCode('delete');
                    } else {
                        $auth = $this->getAuthCode($publicMethod);
                    }
                    $model = new \SplFileObject($filePath, 'w+');
                    $model->fwrite($this->template($auth));
                }
            }
        }
    }

    /**
     * @param string $model
     *
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
            if (in_array($methodName, ['index', 'create', 'store'])) {
                $code = '\\' . $this->model::class . '::class)';
            } else {
                $modelName = (new \ReflectionClass($this->model))->getShortName();
                $code = '$this->route(\'' . strtolower($modelName) . '\'))';
            }
            $auth = 'auth()->user()->can(\'' . $methodName . '\', ' . $code;
        }

        return $auth;
    }
}
