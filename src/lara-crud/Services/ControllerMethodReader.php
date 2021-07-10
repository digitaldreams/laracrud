<?php

namespace LaraCrud\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Routing\Route;

class ControllerMethodReader
{
    /**
     * List of full namespaces that will be import on top of controller.
     *
     * @var array
     */
    protected array $namespaces = [];

    /**
     * @var \ReflectionMethod
     */
    protected $reflectionMethod;

    /**
     * @var \Illuminate\Routing\Route
     */
    protected $route;

    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $parentModel;

    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    public array $authMiddleware = ['auth', 'auth:sanctum', 'auth:api'];

    /**
     * @var bool
     */
    protected bool $isSanctumAuth = false;

    /**
     * @var bool
     */
    protected bool $isPassportAuth = false;

    /**
     * @var bool
     */
    protected bool $isWebAuth = false;


    protected ModelRelationReader $modelRelationReader;

    public static array $ignoreDataProviderRules = [
        'nullable',
        'string',
        'numeric',
    ];

    protected array $validationRules;

    protected string $routeString;

    protected bool $hasModelOnParameter = false;

    protected string $parentVariable = '';

    protected bool $hasModelParentOnParameter = false;

    protected array $fileFieldNames = [];

    /**
     * ControllerMethod constructor.
     *
     * @param \ReflectionMethod         $reflectionMethod
     * @param \Illuminate\Routing\Route $route
     */
    public function __construct(\ReflectionMethod $reflectionMethod, Route $route)
    {
        $this->reflectionMethod = $reflectionMethod;
        $this->route = $route;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return $this
     */
    public function setModel(Model $model): self
    {
        $this->model = $model;
        $this->modelRelationReader = (new ModelRelationReader($model))->read();
        $this->namespaces[] = get_class($model);

        return $this;
    }

    /**
     * Set Parent Model when creating a child Resource Controller.
     *
     * @param \Illuminate\Database\Eloquent\Model $parentModel
     *
     * @return \LaraCrud\Builder\Test\Methods\ControllerMethod
     */
    public function setParent(Model $parentModel): self
    {
        $this->parentModel = $parentModel;
        $this->namespaces[] = get_class($parentModel);

        return $this;
    }

    protected function parseRoute(): string
    {
        $params = '';
        $name = $this->route->getName();
        if (empty($this->route->parameterNames())) {
            return 'route("' . $name . '")';
        }
        foreach ($this->route->parameterNames() as $parameterName) {
            if (strtolower($parameterName) == strtolower($this->modelRelationReader->getShortName())) {
                $value = $this->getModelVariable() . '->' . $this->model->getRouteKeyName();
                $this->hasModelOnParameter = true;
            } else {
                if ($this->parentModel) {
                    $ref = new \ReflectionClass($this->parentModel);
                    if (strtolower($parameterName) == strtolower($ref->getShortName())) {
                        $this->hasModelParentOnParameter = true;
                        $parentVariable = '$' . lcfirst($ref->getShortName());
                        $value = $parentVariable . '->' . $this->parentModel->getRouteKeyName();
                        $this->parentVariable = sprintf(
                            '%s = %s::factory()->for($user)->create();',
                            $parentVariable,
                            $ref->getShortName()
                        ) . "\n\t\t";
                    }
                } else {
                    $value = '';
                }
            }
            $params .= '"' . $parameterName . '" => ' . $value . ', ';
        }

        return $this->routeString = 'route("' . $name . '",[' . $params . '])';
    }

    /**
     *
     */
    protected function getRoute(): string
    {
        if (!empty($this->routeString)) {
            return $this->routeString;
        }

        return $this->parseRoute();
    }

    /**
     * Get list of importable Namespaces.
     *
     * @return array
     */
    public function getNamespaces(): array
    {
        return $this->namespaces;
    }

    protected function getModelVariable(): string
    {
        return '$' . lcfirst($this->modelRelationReader->getShortName());
    }

    protected function getParentVariable(): string
    {
        if ($this->parentModel) {
            $ref = new \ReflectionClass($this->parentModel);
            return '$' . lcfirst($ref->getShortName());
        }
        return '';
    }


    public function getCustomRequestClassRules(): array
    {
        if (!empty($this->validationRules)) {
            return $this->validationRules;
        }
        $rules = [];
        try {
            foreach ($this->reflectionMethod->getParameters() as $parameter) {
                if ($parameter->hasType()) {
                    if (is_subclass_of($parameter->getType()->getName(), FormRequest::class)) {
                        $className = $parameter->getType()->getName();
                        $rfm = new \ReflectionMethod($parameter->getType()->getName(), 'rules');
                        $rules = $rfm->invoke(new $className());
                    }
                }
            }
        } catch (\Exception $e) {
            return $rules;
        }

        return $this->validationRules = $rules;
    }

    /**
     * @return bool
     */
    protected function hasFile(): bool
    {
        $rules = $this->getCustomRequestClassRules();
        foreach ($rules as $field => $rule) {
            $listOfRules = is_array($rule) ? $rule : explode("|", $rule);
            foreach ($listOfRules as $listOfRule) {
                if (is_object($listOfRule)) {
                    continue;
                }
                $mimeTypes = substr_compare($listOfRule, 'mimetypes', 0, 9);
                $mimes = substr_compare($listOfRule, 'mimes', 0, 5);
                $dimensions = substr_compare($listOfRule, 'dimensions', 0, 10);
                if ('image' == $listOfRule || 'file' == $listOfRule || $mimes == 0 || $mimeTypes == 0 || $dimensions == 0) {
                    return true;
                }
            }
        }

        return false;
    }
}
