<?php


namespace LaraCrud\Builder\Test\Methods;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Route;

class ControllerMethod
{

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
     * Get Inside code of a Controller Method.
     *
     * @return string
     *
     * @throws \ReflectionException
     */
    public function getCode(): string
    {

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
     * Set Parent Model when creating a child Resource Controller.
     *
     * @param \Illuminate\Database\Eloquent\Model $parentModel
     *
     * @return \LaraCrud\Builder\Test\Methods\ControllerMethod
     */
    public function setParent(Model $parentModel): self
    {
        $this->parentModel = $parentModel;
        $this->namespaces[] = 'use ' . get_class($parentModel);

        return $this;
    }
}
