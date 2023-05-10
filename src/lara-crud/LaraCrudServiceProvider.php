<?php

namespace LaraCrud;

use DbReader\Database;
use Illuminate\Support\ServiceProvider;
use LaraCrud\Command\ControllerCommand;
use LaraCrud\Command\FactoryCommand;
use LaraCrud\Command\MigrationCommand;
use LaraCrud\Command\ModelCommand;
use LaraCrud\Command\MvcCommand;
use LaraCrud\Command\PackageCommand;
use LaraCrud\Command\PolicyCommand;
use LaraCrud\Command\ReactJs\ApiEndpointCommand;
use LaraCrud\Command\ReactJs\CrudCommand;
use LaraCrud\Command\ReactJs\EnumCommand;
use LaraCrud\Command\ReactJs\FormCommand;
use LaraCrud\Command\ReactJs\ModelCommand as ReactJsModelCommand;
use LaraCrud\Command\ReactJs\ServiceCommand;
use LaraCrud\Command\RequestCommand;
use LaraCrud\Command\ResourceCommand;
use LaraCrud\Command\RouteCommand;
use LaraCrud\Command\TestCommand;
use LaraCrud\Command\ViewCommand;
use Illuminate\Contracts\Support\DeferrableProvider;
/**
 * Description of LaraCrudServiceProvider.
 *
 * @author Tuhin
 */
class LaraCrudServiceProvider extends ServiceProvider implements DeferrableProvider
{

    /**
     * List of command which will be registered.
     *
     */
    protected array $commands = [
        ModelCommand::class,
        FactoryCommand::class,
        RequestCommand::class,
        ControllerCommand::class,
        RouteCommand::class,
        MigrationCommand::class,
        ViewCommand::class,
        MvcCommand::class,
        PolicyCommand::class,
        TestCommand::class,
        PackageCommand::class,
        ResourceCommand::class,
        EnumCommand::class,
        ReactJsModelCommand::class,
        ServiceCommand::class,
        FormCommand::class,
        ApiEndpointCommand::class,
        CrudCommand::class,
    ];

    /**
     * Run on application loading.
     */
    public function boot(): void
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
    public function register(): void
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
    public function provides(): array
    {
        return ['laracrud', 'reactjs'];
    }
}
