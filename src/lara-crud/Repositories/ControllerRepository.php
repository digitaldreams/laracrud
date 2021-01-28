<?php

namespace LaraCrud\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use LaraCrud\Services\Controller\ControllerMethod;
use LaraCrud\Services\Controller\CreateMethod;
use LaraCrud\Services\Controller\DestroyMethod;
use LaraCrud\Services\Controller\EditMethod;
use LaraCrud\Services\Controller\ForceDeleteMethod;
use LaraCrud\Services\Controller\IndexMethod;
use LaraCrud\Services\Controller\RestoreMethod;
use LaraCrud\Services\Controller\ShowMethod;
use LaraCrud\Services\Controller\StoreMethod;
use LaraCrud\Services\Controller\UpdateMethod;

class ControllerRepository
{
    /**
     * @var string[]
     */
    public static $methodClassMapper = [
        'index' => IndexMethod::class,
        'show' => ShowMethod::class,
        'create' => CreateMethod::class,
        'store' => StoreMethod::class,
        'edit' => EditMethod::class,
        'update' => UpdateMethod::class,
        'destroy' => DestroyMethod::class,
        'restore' => RestoreMethod::class,
        'forceDelete' => ForceDeleteMethod::class,
    ];

    /**
     * @var bool
     */
    protected bool $softDeleteAble = false;

    /**
     * @var ControllerMethod[]
     */
    protected array $methods = [];

    /**
     * All code generated my ControllerMethod classes and stored here as array of string.
     *
     * @var string[]
     */
    protected array $code = [];

    /**
     * List of Class Full Namespace that should be imported on top of Class.
     *
     * @var array
     */
    protected array $importableNamespaces = [];

    /**
     * @param \LaraCrud\Services\Controller\ControllerMethod $method
     *
     * @return \LaraCrud\Repositories\ControllerRepository
     */
    public function addMethod(ControllerMethod $method): self
    {
        $this->methods[] = $method;

        return $this;
    }

    /**
     * @param string[]                                 $methods
     * @param \Illuminate\Database\Eloquent\Model      $model
     * @param \Illuminate\Database\Eloquent\Model|null $parent
     *
     * @return $this
     */
    public function addMethodsFromString(array $methods, Model $model, ?Model $parent = null): self
    {
        $insertAbleMethods = array_intersect_key(static::$methodClassMapper, array_flip($methods));
        foreach ($insertAbleMethods as $methodName) {
            $method = new $methodName($model);
            if (!empty($parent)) {
                $method->setParent($parent);
            }
            $this->addMethod($method);
        }
        return $this;
    }

    /**
     * Loop through all of the ControllerMethod classes and getCode and importable NameSpace.
     *
     * @return $this
     */
    public function build(): self
    {
        foreach ($this->methods as $method) {
            try {
                $this->code[] = $method->getCode();
                //Only unique Full Namespace will be imported. Already added will be ignored.
                $this->importableNamespaces = array_unique(array_merge($this->importableNamespaces, array_unique($method->getNamespaces())));
            } catch (\Exception $exception) {
                Log::error($exception->getTraceAsString());
                continue;
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getImportableNamespaces(): array
    {
        return $this->importableNamespaces;
    }

    /**
     * @return string[]
     */
    public function getCode(): array
    {
        return $this->code;
    }

}
