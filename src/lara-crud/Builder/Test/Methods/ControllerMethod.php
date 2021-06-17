<?php

namespace LaraCrud\Builder\Test\Methods;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Routing\Route;
use Illuminate\Support\Str;
use LaraCrud\Services\ModelRelationReader;

abstract class ControllerMethod
{
    protected array $testMethods = [];

    /**
     * List of full namespaces that will be import on top of controller.
     *
     * @var array
     */
    protected array $namespaces = [];

    /**
     * Whether its an API method or not.
     *
     * @var bool
     */
    protected bool $isApi = false;

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

    /**
     * @var string
     */
    protected string $modelFactory;

    /**
     * @var string
     */
    protected string $parentModelFactory;

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

    /**
     * @var bool
     */
    public static bool $hasSuperAdminRole = false;


    protected ModelRelationReader $modelRelationReader;

    public static array $ignoreDataProviderRules = [
        'nullable',
        'string',
        'numeric',
    ];

    protected array $fake = [];

    protected array $endFake = [];

    protected array $validationRules;

    protected string $routeString;

    protected bool $hasModelOnParameter;

    protected string $parentVariable = '';

    protected bool $hasModelParentOnParameter;

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
     * @return static
     */
    abstract public function before();

    /**
     * Get Inside code of a Controller Method.
     *
     * @return string
     *
     * @throws \ReflectionException
     */
    public function getCode(): string
    {
        $this->parseRoute();
        $this->hasFile()->before();

        return implode("\n", $this->testMethods);
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

    /**
     * @return string
     */
    protected function getModelFactory(): string
    {
        return $this->modelFactory;
    }

    /**
     * @return string
     */
    protected function getParentModelFactory(): string
    {
        return $this->parentModelFactory;
    }

    /**
     * Whether Current route need Auth.
     *
     * @return bool
     */
    protected function isAuthRequired(): bool
    {
        $auth = array_intersect($this->authMiddleware, $this->route->gatherMiddleware());

        if (count($auth) > 0) {
            if (in_array('auth', $auth)) {
                $this->isWebAuth = true;
            }
            if (in_array('auth:sanctum', $auth)) {
                $this->isSanctumAuth = true;
            }
            if (in_array('auth:api', $auth)) {
                $this->isPassportAuth = true;
            }

            return true;
        }

        return false;
    }

    /**
     * @return false|string
     */
    protected function getSanctumActingAs($actionAs)
    {
        if (! $this->isSanctumAuth) {
            return false;
        }
        $this->namespaces[] = 'Laravel\Sanctum\Sanctum';

        return 'Sanctum::actingAs(' . $actionAs . ', [\'*\']);';
    }

    /**
     * @return false|string
     */
    protected function getPassportActingAs($actionAs)
    {
        if (! $this->isPassportAuth) {
            return false;
        }

        $this->namespaces[] = 'Laravel\Passport\Passport';

        return 'Passport::actingAs(' . $actionAs . ', [\'*\']);';
    }

    protected function getWebAuthActingAs($actionAs)
    {
        if (! $this->isWebAuth) {
            return false;
        }

        return 'actingAs(' . $actionAs . ')->';
    }

    /**
     * Whether current application has Super Admin Role.
     *
     * @return bool
     */
    protected function hasSuperAdminRole(): bool
    {
        return static::$hasSuperAdminRole;
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
        if (! empty($this->routeString)) {
            return $this->routeString;
        }

        return $this->parseRoute();
    }

    protected function getModelVariable(): string
    {
        return '$' . lcfirst($this->modelRelationReader->getShortName());
    }

    protected function getApiActingAs(string $actionAs)
    {
        if ($this->isSanctumAuth) {
            return $this->getSanctumActingAs($actionAs);
        }
        if ($this->isPassportAuth) {
            return $this->getPassportActingAs($actionAs);
        }

        return '';
    }

    protected function getGlobalVariables($actionAs = '$user'): array
    {
        return [
            'modelVariable' => $this->getModelVariable(),
            'modelShortName' => $this->modelRelationReader->getShortName(),
            'route' => $this->getRoute(),
            'modelMethodName' => Str::snake($this->modelRelationReader->getShortName()),
            'apiActingAs' => $this->getApiActingAs($actionAs),
            'webActingAs' => $this->isWebAuth ? $this->getWebAuthActingAs($actionAs) : '',
            'table' => $this->model->getTable(),
            'assertDeleted' => $this->modelRelationReader->isSoftDeleteAble() ? 'assertSoftDeleted' : 'assertDeleted',
            'fake' => implode("\n", array_unique($this->fake)),
            'endFake' => implode("\n", array_unique($this->endFake)),
            'parentVariable' => $this->parentVariable,

        ];
    }


    public function getCustomRequestClassRules(): array
    {
        if (! empty($this->validationRules)) {
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

    public function generatePostData($update = false): string
    {
        $data = '';
        $modelVariable = $update == true ? '$new' . $this->modelRelationReader->getShortName() : $this->getModelVariable();
        $rules = $this->getCustomRequestClassRules();
        foreach ($rules as $field => $rule) {
            $data .= "\t\t\t" . '"' . $field . '" => ' . $modelVariable . '->' . $field . ',' . PHP_EOL;
        }

        return $data;
    }

    /**
     * @return string
     */
    public function generateDataProvider(): string
    {
        $data = '';
        $rules = $this->getCustomRequestClassRules();
        foreach ($rules as $field => $rule) {
            $listOfRules = is_array($rule) ? $rule : explode("|", $rule);
            foreach ($listOfRules as $listOfRule) {
                if (is_object($listOfRule)) {
                    continue;
                }
                if (in_array($listOfRule, static::$ignoreDataProviderRules)) {
                    continue;
                }
                $data .= "\t\t\t" . '"' . "The $field must be $listOfRule" . '"' . ' => ["' . $field . '"," " ],' . PHP_EOL;
            }
        }

        return $data;
    }

    protected function hasFile(): self
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
                    $this->namespaces[] = 'Illuminate\Support\Facades\Storage';
                    $this->namespaces[] = 'Illuminate\Http\UploadedFile';
                    $this->fake[] = 'Storage::fake(\'file\');';
                    $this->fake[] = '$file = UploadedFile::fake()->create(\'poster.jpg\');';

                    $this->endFake[] = 'Storage::disk(\'file\')->assertExists(\'photo1.jpg\');';
                }
            }
        }

        return $this;
    }
}
