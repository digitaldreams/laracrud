<?php

namespace LaraCrud;

use LaraCrud\Builder\Controller\Web\CreateMethod;
use LaraCrud\Builder\Controller\Web\DestroyMethod;
use LaraCrud\Builder\Controller\Web\EditMethod;
use LaraCrud\Builder\Controller\Web\ForceDeleteMethod;
use LaraCrud\Builder\Controller\Web\IndexMethod;
use LaraCrud\Builder\Controller\Web\RestoreMethod;
use LaraCrud\Builder\Controller\Web\ShowMethod;
use LaraCrud\Builder\Controller\Web\StoreMethod;
use LaraCrud\Builder\Controller\Web\UpdateMethod;

/**
 * All of the Global configuration will be handled from this Class instead of direct call of config().
 */
class Configuration
{
    /**
     * @var array
     */
    public static array $controllerWebMethods = [
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

    public static array $controllerApiMethods = [

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

    /**
     * @return string
     */
    public static function getViewPath(): string
    {
        return !empty(static::$viewPath) ? static::$viewPath : config('laracrud.view.path');
    }
}
