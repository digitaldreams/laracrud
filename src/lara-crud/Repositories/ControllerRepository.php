<?php

namespace LaraCrud\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use LaraCrud\Services\Controller\ControllerMethod;

class ControllerRepository
{
    /**
     * @var Model
     */
    protected $model;

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
     * ControllerRepository constructor.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Whether given model implement SoftDeletes trait.
     * If so then we have to add restore and forceDelete methods as well.
     *
     * @return bool
     */
    public function isSoftDeleteAble(): bool
    {
        return in_array(SoftDeletes::class, class_uses($this->model));
    }

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
     * Loop through all of the ControllerMethod classes and getCode and importable NameSpace
     *
     * @return $this
     */
    public function build(): self
    {
        foreach ($this->methods as $method) {
            try {
                if ($method instanceof ControllerMethod) {
                    $this->code[] = $method->getCode();
                    $this->importableNamespaces = array_merge($this->importableNamespaces, $method->getNamespaces());
                }
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
