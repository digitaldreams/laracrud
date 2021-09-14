<?php

namespace LaraCrud;

use DbReader\Database;
use Illuminate\Support\ServiceProvider;
use LaraCrud\Console\Controller;
use LaraCrud\Console\Factory;
use LaraCrud\Console\Migration;
use LaraCrud\Console\Model;
use LaraCrud\Console\Mvc;
use LaraCrud\Console\Package;
use LaraCrud\Console\Policy;
use LaraCrud\Console\ReactJs\ApiEndpointCommand;
use LaraCrud\Console\ReactJs\EnumCommand;
use LaraCrud\Console\ReactJs\FormCommand;
use LaraCrud\Console\ReactJs\ModelCommand;
use LaraCrud\Console\ReactJs\ServiceCommand;
use LaraCrud\Console\Request;
use LaraCrud\Console\Resource;
use LaraCrud\Console\Route;
use LaraCrud\Console\Test;
use LaraCrud\Console\View;

/**
 * Description of LaraCrudServiceProvider.
 *
 * @author Tuhin
 */
class LaraCrudServiceProvider extends ServiceProvider
{
    protected $defer = true;

    /**
     * List of command which will be registered.
     *
     * @var array
     */
    protected $commands = [
        Model::class,
        Factory::class,
        Request::class,
        Controller::class,
        Route::class,
        Migration::class,
        View::class,
        Mvc::class,
        Policy::class,
        Test::class,
        Package::class,
        Resource::class,
        EnumCommand::class,
        ModelCommand::class,
        ServiceCommand::class,
        FormCommand::class,
        ApiEndpointCommand::class,
    ];

    /**
     * Run on application loading.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/laracrud.php' => config_path('laracrud.php'),
        ], 'laracrud-config');

        // Publish Templates to view/vendor folder so user can customize this own templates
        $this->publishes([
            __DIR__ . '/../../resources/templates' => resource_path('views/vendor/laracrud/templates'),
        ], 'laracrud-template');

        $this->publishes([
            __DIR__ . '/../../resources/assets' => public_path('laracrud'),
            __DIR__ . '/../../resources/views' => resource_path('views/laracrud'),
        ], 'laracrud-assets');
    }

    /**
     * Run after all boot method completed.
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/laracrud.php',
            'laracrud'
        );
        foreach (config('laracrud.binds') as $contract => $repository) {
            $this->app->bind($contract, $repository);
        }
        if ($this->app->runningInConsole()) {
            //DbReader\Database settings
            Database::settings([
                'pdo' => app('db')->connection()->getPdo(),
                'manualRelations' => config('laracrud.model.relations', []),
                'ignore' => config('laracrud.view.ignore', []),
                'protectedColumns' => config('laracrud.model.protectedColumns', []),
                'files' => config('laracrud.image.columns', []),
            ]);
            $this->commands($this->commands);
        }
    }

    /**
     * To register laracrud as first level command. E.g. laracrud:model.
     *
     * @return array
     */
    public function provides()
    {
        return ['laracrud', 'reactjs'];
    }
}
