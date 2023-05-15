<?php

namespace LaraCrud\Generators;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use LaraCrud\Contracts\ClassGeneratorContract;
use LaraCrud\Contracts\FileGeneratorContract;
use LaraCrud\Helpers\ClassInspector;
use LaraCrud\Helpers\Helper;
use LaraCrud\Helpers\NamespaceResolver;
use LaraCrud\Helpers\TemplateManager;
use \Illuminate\Database\Eloquent\Model as EloquentModel;

class RequestController implements FileGeneratorContract
{
    use Helper;


    protected $table;

    protected EloquentModel $model;

    protected string $controllerNs = '';

    protected string $controllerName;

    protected ClassInspector $classInspector;

    protected string $namespace;
    /**
     * Name of the folder where Request Classes will be saved.
     *
     */
    protected string $folderName = '';

    protected string $template;

    /**
     * @var array
     */
    protected array $methods = ['index', 'show', 'create', 'store', 'update', 'destroy'];
    protected ?string $modelName;
    private bool $policy;

    public function __construct(EloquentModel $model, ?string $controller = null, bool $api = false, ?string $name = null)
    {
        $this->model = $model;
        $policies = Gate::policies();
        $this->policy = $policies[$this->model::class] ?? false;

        $this->controllerNs = NamespaceResolver::getControllerRoot($api);
        $this->table = $model->getTable();
        $this->folderName = !empty($name) ? $name : $this->table;
        $this->template = !empty($api) ? 'api' : 'web';

        if (!empty($controller)) {
            if (!class_exists($controller)) {
                $this->controllerName = $this->controllerNs . '\\' . $controller;
            }

            if (!class_exists($this->controllerName)) {
                throw new \Exception('Controller ' . $this->controllerName . ' does not exists');
            }

            $this->classInspector = new ClassInspector($this->controllerName);
            $requestNs = NamespaceResolver::getRequestRoot($api);
            $this->namespace = (trim((string)$requestNs, '/')) . '\\' . ucfirst(
                    Str::camel($this->folderName)
                );
            $this->modelName = $this->getModelName($this->table);
        }
    }

    /**
     * Process template and return complete code.
     *
     * @param string $authorization
     *
     * @return mixed
     */
    public function template(string $authorization = 'true')
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
        NamespaceResolver::checkPath($this->namespace.'\\'.$this->folderName, '');
        $publicMethods = $this->classInspector->publicMethods;

        if (!empty($publicMethods)) {
            foreach ($publicMethods as $publicMethod) {
                $folderPath = base_path(NamespaceResolver::toPath($this->namespace));
                $this->modelName = $this->getModelName($publicMethod);
                $filePath = $folderPath . '/' . $this->modelName . '.php';

                if (file_exists($filePath)) {
                    continue;
                }
                $isApi = 'api' === $this->template ? true : false;
                if (in_array($publicMethod, ['create', 'store'])) {
                    $requestStore = new Request(
                        $this->model,
                        ucfirst(Str::camel($this->folderName)) . '/' . $this->modelName,
                        $isApi
                    );
                    $requestStore->setAuthorization($this->getAuthCode('create'));
                    $requestStore->save();
                } elseif (in_array($publicMethod, ['edit', 'update'])) {
                    $requestUpdate = new Request(
                        $this->model,
                        ucfirst(Str::camel($this->folderName)) . '/' . $this->modelName,
                        $isApi
                    );
                    $requestUpdate->setAuthorization($this->getAuthCode('update'));
                    $requestUpdate->save();
                } else {
                    $auth = 'true';
                    if ('show' === $publicMethod) {
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

    private function getAuthCode(string $methodName): string
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
