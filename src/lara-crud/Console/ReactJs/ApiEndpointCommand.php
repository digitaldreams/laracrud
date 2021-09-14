<?php

namespace LaraCrud\Console\ReactJs;

use Illuminate\Console\Command;
use LaraCrud\Crud\ReactJs\ReactJsApiEndpointCrud;
use LaraCrud\Helpers\Helper;

class ApiEndpointCommand extends Command
{
    use Helper;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reactjs:apiEndpoint {controller}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'apiEndpoint generator based on Controller Class';

    public function handle()
    {
        try {
            $apiEndpointCrud = $this->initReactJsApiEndpoint();
            $apiEndpointCrud->save();
            $this->info('ApiEndpoint file generated successfully');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

    }

    /**
     * @throws \ReflectionException
     */
    protected function initReactJsApiEndpoint(): ReactJsApiEndpointCrud
    {
        $controller = $this->argument('controller');

        if (! class_exists($controller)) {
            $namespace = config('laracrud.controller.apiNamespace');
            $namespace = $this->getFullNS($namespace);
            $controller = rtrim($namespace, '\\') . '\\' . $controller;
        }

        if (! class_exists($controller)) {
            $this->error(sprintf('%s controller does not exists', $controller));
            exit();
        }


        return new ReactJsApiEndpointCrud($controller);
    }
}
