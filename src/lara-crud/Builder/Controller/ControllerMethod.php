<?php

namespace LaraCrud\Builder\Controller;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use LaraCrud\Contracts\Controller\ApiArrayResponseMethod;
use LaraCrud\Contracts\Controller\ApiResourceResponseMethod;
use LaraCrud\Contracts\Controller\RedirectAbleMethod;
use LaraCrud\Contracts\Controller\ViewAbleMethod;
use LaraCrud\Generators\ApiResource;
use LaraCrud\Helpers\ApiMethodHelper;
use LaraCrud\Helpers\Helper;
use LaraCrud\Helpers\NamespaceResolver;
use LaraCrud\Helpers\RedirectAbleMethodHelper;
use LaraCrud\Helpers\ViewAbleMethodHelper;
use LaraCrud\Traits\ModelShortNameAndVariablesTrait;
use ReflectionClass;

abstract class ControllerMethod
{
    use ViewAbleMethodHelper;
    use RedirectAbleMethodHelper;
    use Helper;
    use ModelShortNameAndVariablesTrait;
    use ApiMethodHelper;


    /**
     * List of full namespaces that will be import on top of controller.
     *
     * @var array
     */
    protected array $namespaces = [];


    protected ReflectionClass $modelReflectionClass;

    /**
     * Whether its an API method or not.
     *
     */
    protected bool $isApi = false;

    /**
     * Full Namespace Request folder where system will find custom Request class or save into it.
     *
     * @var string
     */
    protected string $requestFolderNs;

    /**
     * Name of the controller method.
     *
     */
    protected ?string $methodName;

    /**
     * @var string[]
     */
    protected $policyMethodmapper = [
        //Controller Method => Policy Method
        'index' => 'viewAny',
        'show' => 'view',
        'destroy' => 'delete',
        'edit' => 'update',
        'store' => 'create',
    ];

    /**
     * ControllerMethod constructor.
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->modelReflectionClass = new \ReflectionClass($model);

        if ($this instanceof ApiResourceResponseMethod) {
            $requestNs = config('laracrud.request.apiNamespace');

            $this->isApi = true;
        } else {
            $requestNs = config('laracrud.request.namespace');
        }

        $this->requestFolderNs = NamespaceResolver::getFullNS($requestNs) . '\\' . ucfirst(Str::camel($this->model->getTable()));
    }

    /**
     * Name of Controller Method.
     */
    public function getMethodName(): string
    {
        if (!empty($this->methodName)) {
            return $this->methodName;
        }
        $reflection = new \ReflectionClass(static::class);

        return $this->methodName = str_replace('Method', '', lcfirst($reflection->getShortName()));
    }

    /**
     * Will be called before getViewGenerateCode method call to setup necessary parameters and variables.
     *
     * @throw \Exception
     *
     * @return $this
     *
     * @throws \ReflectionException
     */
    protected function beforeGenerate(): self
    {
        return $this->setParentVariableAndParam();
    }

    /**
     * @return $this
     */
    public function setMethodName(string $name): self
    {
        $this->methodName = $name;

        return $this;
    }

    /**
     * Get Inside code of a Controller Method.
     *
     *
     * @throws \ReflectionException
     */
    public function getCode(): string
    {
        if ($this instanceof ViewAbleMethod) {
            return $this->beforeGenerate()->generateViewCode();
        } elseif ($this instanceof RedirectAbleMethod) {
            return $this->beforeGenerate()->generateRedirectAbleCode();
        } elseif ($this instanceof ApiResourceResponseMethod) {
            return $this->beforeGenerate()->generateApiResourceCode();
        } elseif ($this instanceof ApiArrayResponseMethod) {
            return $this->beforeGenerate()->generateApiArrayCode();
        }

        return '';
    }

    /**
     * Get list of importable Namespaces.
     */
    public function getNamespaces(): array
    {
        return $this->namespaces;
    }

    /**
     * Which Request class will be used in method argument.
     */
    protected function getRequestClass(): string
    {
        $customRequestName = ucfirst($this->getMethodName()) . config('laracrud.request.classSuffix', 'Request');
        $fullRequestNs = $this->requestFolderNs . '\\' . $customRequestName;

        if (class_exists($fullRequestNs)) {
            $requestClass = $customRequestName;
            $this->namespaces[] = $fullRequestNs;
        } else {
            $requestClass = 'Request';
        }

        return $requestClass;
    }

    /**
     * Set Parent Model when creating a child Resource Controller.
     *
     *
     * @return $this
     */
    public function setParent(Model $parentModel): self
    {
        $this->parentModel = $parentModel;
        $this->namespaces[] = 'use ' . $parentModel::class;

        return $this;
    }

    public function getBody(): string
    {
        return '';
    }

    /**
     * This is a helper function which will set Parent variable name and its method argument.
     *
     * @return $this
     *
     * @throws \ReflectionException
     */
    protected function setParentVariableAndParam(): self
    {
        if ($this->parentModel) {
            $this->setVariable($this->getParentVariableName(), '$' . $this->getParentVariableName())
                ->setParameter($this->getParentShortName(), '$' . $this->getParentVariableName());
        }

        return $this;
    }

    public function phpDocComment(): string
    {
        return '';
    }

    public function getAuthorization(): string
    {
        $policies = Gate::policies();
        $policy = $policies[$this->model::class] ?? false;
        $policyMethod = $this->policyMethodmapper[$this->getMethodName()] ?? $this->getMethodName();
        if (class_exists($policy) && method_exists($policy, $policyMethod)) {
            return $this->getAuthCode($policyMethod);
        }
        return '';
    }


    private function getAuthCode(string $methodName): string
    {
        $auth = '';
        if (in_array($methodName, ['viewAny', 'create', 'store'])) {
            $code = $this->modelShortName . '::class';
        } else {
            $code = '$' . $this->getModelVariableName();
        }
        return '$this->authorize(\'' . $methodName . '\', ' . $code . ');' . "\n";
    }


    public function isCollection(): bool
    {
        return false;
    }

    public function resource(): string
    {
        $resourceNs = config('laracrud.resource.namespace', 'App\Http\Resources');
        $classSuffix = config('laracrud.resource.classSuffix', 'Resource');
        $resourceName = $this->getModelShortName() . $classSuffix;
        $fullNs = $resourceNs . '\\' . $resourceName;
        if (!class_exists($fullNs)) {
            $resourceCrud = new ApiResource($this->model);
            $resourceCrud->save();
            $fullNs = $resourceCrud->getFullName();
            $resourceName = $resourceCrud->modelName;
        }
        $this->namespaces[] = $fullNs;

        return $resourceName;
    }

    public function generateResourceResponse(): string
    {
        $resourceName = $this->resource();
        if ($this->isCollection()) {
            return "$resourceName::" . 'collection($builder->paginate(10))';
        }
        return 'new ' . $resourceName . '($' . $this->getModelVariableName() . ')';
    }
}
