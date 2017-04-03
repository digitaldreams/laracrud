<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace LaraCrud;

use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Support\ServiceProvider;
use LaraCrud\Console\Assets;
use LaraCrud\Console\Chart;
use LaraCrud\Console\Controller;
use LaraCrud\Console\Migration;
use LaraCrud\Console\Model;
use LaraCrud\Console\Mvc;
use LaraCrud\Console\Request;
use LaraCrud\Console\Route;
use LaraCrud\Console\View;

/**
 * Description of LaraCrudServiceProvider
 *
 * @author Tuhin
 */
class LaraCrudServiceProvider extends ServiceProvider
{
    protected $defer = true;
    /**
     * List of command which will be registered.
     * @var array
     */
    protected $commands = [
        Controller::class,
        Migration::class,
        Model::class,
        Mvc::class,
        Request::class,
        Route::class,
        View::class,
        Assets::class
    ];

    public function boot()
    {

        return $this->publishes([
            __DIR__ . '/../config/laracrud.php' => config_path('laracrud.php')
        ], 'config');


    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/laracrud.php', 'laracrud'
        );

        if ($this->app->runningInConsole()) {
            $this->commands($this->commands);

            $this->publishes([
                __DIR__ . '/resources/views/layouts' => resource_path('views/layouts')
            ], 'laracrud-layouts');

            $this->publishes([
                __DIR__ . '/resources/assets/css' => public_path('css')
            ], 'laracrud-layouts');

            $this->publishes([
                __DIR__ . '/resources/assets/js' => public_path('js')
            ], 'laracrud-layouts');

            $this->publishes([
                __DIR__ . '/resources/assets/fonts' => public_path('fonts')
            ], 'laracrud-layouts');

        }
    }

    public function provides()
    {
        return ['laracrud'];
    }
}