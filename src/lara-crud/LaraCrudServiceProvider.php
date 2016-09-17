<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace LaraCrud;

use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Support\ServiceProvider;

/**
 * Description of LaraCrudServiceProvider
 *
 * @author Tuhin
 */
class LaraCrudServiceProvider extends ServiceProvider
{
    protected $defer = true;

    public function boot()
    {

        $this->publishes([
            __DIR__.'/config/laracrud.php' => config_path('laracrud.php')
            ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/laracrud.php', 'laracrud'
        );
    }
}