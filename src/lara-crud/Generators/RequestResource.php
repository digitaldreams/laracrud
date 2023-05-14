<?php

namespace LaraCrud\Generators;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use LaraCrud\Contracts\ClassGeneratorContract;
use LaraCrud\Helpers\Helper;
use LaraCrud\Helpers\NamespaceResolver;
use LaraCrud\Helpers\TemplateManager;
use \Illuminate\Database\Eloquent\Model as EloquentModel;
use LaraCrud\Services\Mapper;

class RequestResource implements ClassGeneratorContract
{
    use Helper;

    protected string $table;

    /**
     * Request Class parent Namespace.
     *
     */
    protected string $namespace;

    /**
     * Name of the folder where Request Classes will be saved.
     *
     * @var string
     */
    protected string $folderName = '';


    protected array $methods = ['store', 'update'];

    protected string $template = '';

    protected $model;

    protected string $policy;
    protected ?string $modelName;

    public function __construct(EloquentModel $model, string|array $only = '', ?bool $api = false, ?string $name = '')
    {
        $this->table = $model->getTable();
        $this->model = $model;
        $policies = Gate::policies();
        $this->policy = $policies[$this->model::class] ?? false;
        $this->folderName = !empty($name) ? $name : $this->table;

        if (!empty($only) && is_array($only)) {
            $this->methods = $only;
        }
        $ns = NamespaceResolver::getRequestRoot($api);
        $this->namespace = trim((string)$ns, '/') . '\\' . ucfirst(
                Str::camel($this->folderName)
            );
        $this->modelName = $this->getModelName($this->table);
        $this->template = $api === true ? 'api' : 'web';
    }

    /**
     * Process template and return complete code.
     *
     */
    public function template(string $authorization = 'true'): string
    {
        $tempMan = new TemplateManager('request/' . $this->template . '/template.txt', [
            'namespace' => $this->namespace,
            'requestClassName' => $this->modelName,
            'authorization' => $authorization,
            'rules' => implode("\n", []),
        ]);

        return $tempMan->get();
    }

    public function save()
    {
        NamespaceResolver::checkPath($this->getNamespace(), $this->getClassName());
        $publicMethods = $this->methods;

        if (!empty($publicMethods)) {
            foreach ($publicMethods as $publicMethod) {
                $folderPath = base_path(NamespaceResolver::toPath($this->namespace));
                $this->modelName = $this->getModelName($publicMethod);
                $filePath = $folderPath . '/' . $this->modelName . '.php';

                if (file_exists($filePath)) {
                    continue;
                }
                $isApi = 'api' === $this->template ? true : false;

                if ('store' === $publicMethod) {
                    $requestStore = new Request(
                        $this->model,
                        ucfirst(Str::camel($this->folderName)) . '/StoreRequest',
                        $isApi
                    );
                    $requestStore->setAuthorization($this->getAuthCode('create'));
                    $requestStore->save();
                    Mapper::loadByModel($this->model, [
                        'storeRequest' => $this->getNamespace() . '\\' . ucfirst(
                                Str::camel($this->folderName)
                            ) . '\\StoreRequest'
                    ])->save();
                } elseif ('update' === $publicMethod) {
                    $requestUpdate = new Request(
                        $this->model,
                        ucfirst(Str::camel($this->folderName)) . '/UpdateRequest',
                        $isApi
                    );
                    $requestUpdate->setAuthorization($this->getAuthCode('update'));
                    $requestUpdate->save();

                    Mapper::loadByModel($this->model, [
                        'updateRequest' => $this->getNamespace() . '\\' . ucfirst(
                                Str::camel($this->folderName)
                            ) . '\\UpdateRequest'
                    ])->save();
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


    public function setModel(string $model = '')
    {
        if (empty($model)) {
            return $this;
        }

        if (!class_exists($model)) {
            $modelNS = NamespaceResolver::getFullNS(config('laracrud.model.namespace'));
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

    public function getClassName(): string
    {
        return '';
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }
}
