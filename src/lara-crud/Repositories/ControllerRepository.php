<?php

namespace LaraCrud\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
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
     * @var array
     */
    protected array $methods = [];

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
     * @return array
     */
    public function build()
    {
        $codes = [];
        foreach ($this->methods as $method) {
            if ($method instanceof ControllerMethod) {
                $codes[] = $method->getCode();
            }
        }

        return $codes;
    }

    /**
     * @return \LaraCrud\Repositories\ControllerRepository
     */
    public function stopSoftDeleteMethods(): self
    {
    }

    /**
     * @param bool $path
     *
     * @return \LaraCrud\Repositories\ControllerRepository
     */
    public function setUploadable($path = false): self
    {
    }

    /**
     * @param null $columns
     *
     * @return \LaraCrud\Repositories\ControllerRepository
     */
    public function setDownloadable($columns = null): self
    {
    }

    /**
     * @param string $path
     */
    public function setViewPath(string $path)
    {
    }
}
