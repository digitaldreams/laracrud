<?php

namespace LaraCrud;

use LaraCrud\Builder\Controller\CreateMethod;
use LaraCrud\Builder\Controller\DestroyMethod;
use LaraCrud\Builder\Controller\EditMethod;
use LaraCrud\Builder\Controller\ForceDeleteMethod;
use LaraCrud\Builder\Controller\IndexMethod;
use LaraCrud\Builder\Controller\RestoreMethod;
use LaraCrud\Builder\Controller\ShowMethod;
use LaraCrud\Builder\Controller\StoreMethod;
use LaraCrud\Builder\Controller\UpdateMethod;

/**
 * All of the Global configuration will be handled from this Class instead of direct call of config().
 */
class Configuration
{
    /**
     * @var array
     */
    public static array $controllerMethods = [
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
     * @var array
     */
    public static array $requestResourceMethods = [];

    /**
     * @var string
     */
    public static string $viewNamespace;

    /**
     * @var string
     */
    public static string $routePrefix;

    /**
     * @var string
     */
    public static string $viewPath;

    /**
     * @return string
     */
    public static function getViewNamespace(): string
    {
        return !empty(static::$viewNamespace) ? static::$viewNamespace : config('laracrud.view.namespace');
    }

    /**
     * @return string
     */
    public static function getRoutePrefix(): string
    {
        return !empty(static::$routePrefix) ? static::$routePrefix : config('laracrud.view.prefix');
    }

    public static function getModelName(string $table)
    {
    }

    public static function getRoutes()
    {
    }
}
