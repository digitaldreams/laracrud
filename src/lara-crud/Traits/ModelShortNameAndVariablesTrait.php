<?php

namespace LaraCrud\Traits;

use Illuminate\Database\Eloquent\Model;

/**
 * ControllerMethods and View class are using these methods and property frequently.
 * So put them here is the best option for re-usability.
 */
trait ModelShortNameAndVariablesTrait
{
    /**
     * Eloquent Model that will be as main model.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * Parent Model.
     *
     * If controller has a parent. For example Comment Model may have Post parent.
     *
     */
    protected ?Model $parentModel;

    /**
     * @var string
     */
    protected string $parentModelShortName;

    /**
     * @var string
     */
    protected string $modelShortName;

    /**
     * Get Model class name without namespace.
     */
    protected function getModelShortName(): string
    {
        if (! empty($this->modelShortName)) {
            return $this->modelShortName;
        }

        return $this->modelShortName = $this->modelReflectionClass->getShortName();
    }

    public function getModelVariableName(): string
    {
        return lcfirst($this->getModelShortName());
    }

    /**
     * @throws \ReflectionException
     */
    public function getParentVariableName(): string
    {
        return lcfirst($this->getParentShortName());
    }

    /**
     * Get Model class Name without namespace.
     *
     *
     * @throws \ReflectionException
     */
    protected function getParentShortName(): string
    {
        if (empty($this->parentModel)) {
            return '';
        }
        if (! empty($this->parentModelShortName)) {
            return $this->parentModelShortName;
        }

        return $this->parentModelShortName = (new \ReflectionClass($this->parentModel))->getShortName();
    }
}
