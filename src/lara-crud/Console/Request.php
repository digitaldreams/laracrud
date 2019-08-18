<?php

namespace LaraCrud\Console;

use Illuminate\Console\Command;
use LaraCrud\Crud\Request as RequestCrud;
use LaraCrud\Crud\RequestController as RequestControllerCrud;
use LaraCrud\Crud\RequestResource as RequestResourceCrud;
use LaraCrud\Helpers\Helper;

class Request extends Command
{
    use Helper;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laracrud:request {model} {name?} {--controller=} {--resource=} {--api}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a request class based on Model';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $modelName = $this->argument('model');
            $modelFullName = $this->modelFullName($modelName);
            $modelObj = new $modelFullName;
            $name = $this->argument('name');
            $controller = $this->option('controller');
            $resource = $this->option('resource');
            $api = $this->option('api');

            if (!empty($controller)) {
                $requestController = new RequestControllerCrud($modelObj, $controller, $api, $name);
                $requestController->save();
                $this->info('Request controller classes created successfully');

            } elseif (!empty($resource)) {
                $methods = $resource === 'all' ? false : explode(",", $resource);
                $requestResource = new RequestResourceCrud($modelObj, $methods, $api, $name);
                $requestResource->save();
                $this->info('Request resource classes created successfully');

            } else {
                $requestCrud = new RequestCrud($modelObj, $name, $api);
                $requestCrud->save();
                $this->info('Request class created successfully');
            }

        } catch (\Exception $ex) {
            $this->error($ex->getMessage() . ' on line ' . $ex->getLine() . ' in ' . $ex->getFile());
        }
    }

    private function modelFullName($model)
    {
        $modelNamespace = $this->getFullNS(config('laracrud.model.namespace', 'App'));
        if (!class_exists($model)) {
            return $modelNamespace . '\\' . $model;
        }
        return false;
    }
}