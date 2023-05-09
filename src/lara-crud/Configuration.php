<?php

namespace LaraCrud;

use LaraCrud\Builder\Controller\Api\DestroyMethod as ApiDestroyMethod;
use LaraCrud\Builder\Controller\Api\ForceDeleteMethod as ApiForceDeleteMethod;
use LaraCrud\Builder\Controller\Api\IndexMethod as ApiIndexMethod;
use LaraCrud\Builder\Controller\Api\RestoreMethod as ApiRestoreMethod;
use LaraCrud\Builder\Controller\Api\ShowMethod as ApiShowMethod;
use LaraCrud\Builder\Controller\Api\StoreMethod as ApiStoreMethod;
use LaraCrud\Builder\Controller\Api\UpdateMethod as ApiUpdateMethod;
use LaraCrud\Builder\Controller\Web\CreateMethod;
use LaraCrud\Builder\Controller\Web\DestroyMethod;
use LaraCrud\Builder\Controller\Web\EditMethod;
use LaraCrud\Builder\Controller\Web\ForceDeleteMethod;
use LaraCrud\Builder\Controller\Web\IndexMethod;
use LaraCrud\Builder\Controller\Web\RestoreMethod;
use LaraCrud\Builder\Controller\Web\ShowMethod;
use LaraCrud\Builder\Controller\Web\StoreMethod;
use LaraCrud\Builder\Controller\Web\UpdateMethod;
use LaraCrud\Builder\Test\Methods\DestroyMethod as TestDestroyMethod;
use LaraCrud\Builder\Test\Methods\IndexMethod as TestIndexMethod;
use LaraCrud\Builder\Test\Methods\ShowMethod as TestShowMethod;
use LaraCrud\Builder\Test\Methods\StoreMethod as TestStoreMethod;
use LaraCrud\Builder\Test\Methods\UpdateMethod as TestUpdateMethod;

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
        'index' => ApiIndexMethod::class,
        'show' => ApiShowMethod::class,
        'store' => ApiStoreMethod::class,
        'update' => ApiUpdateMethod::class,
        'destroy' => ApiDestroyMethod::class,
        'restore' => ApiRestoreMethod::class,
        'forceDelete' => ApiForceDeleteMethod::class,
    ];

    public static array $testApiMethods = [
        'index' => TestIndexMethod::class,
        'show' => TestShowMethod::class,
        'store' => TestStoreMethod::class,
        'update' => TestUpdateMethod::class,
        'destroy' => TestDestroyMethod::class,
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

    public static function getViewNamespace(): string
    {
        return !empty(static::$viewNamespace) ? static::$viewNamespace : config('laracrud.view.namespace');
    }

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

    public static function getViewPath(): string
    {
        return !empty(static::$viewPath) ? static::$viewPath : config('laracrud.view.path');
    }
}
