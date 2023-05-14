<?php

namespace LaraCrud\Commands;

use Illuminate\Console\Command;
use LaraCrud\Helpers\Helper;
use LaraCrud\Helpers\NamespaceResolver;

class PolicyCommand extends Command
{
    use Helper;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laracrud:policy
        {model  : Eloquent model name}
        {--c|controller= : Create policy for all of the public method of this controller. e.g. --controller=PostController}
        {--name= : Custom Name of the Policy. e.g. --name=MyPostPolicy}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a Policy class based on Controller or Model';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $model = $this->argument('model');
            $controller = $this->option('controller');
            $name = $this->option('name');

            $policyCrud = new \LaraCrud\Generators\Policy($this->getModel($model), $this->getController($controller), $name);
            $policyCrud->save();
            $this->info('Policy class created successfully');
        } catch (\Exception $ex) {
            $this->error(sprintf('%s on line %d in %s', $ex->getMessage(), $ex->getLine(), $ex->getFile()));
        }
    }

    private function getController($controller)
    {
        if (! empty($controller) && ! class_exists($controller)) {
            $namespace = config('laracrud.controller.namespace');
            $namespace = NamespaceResolver::getFullNS($namespace);
            $controller = rtrim($namespace, '\\') . '\\' . $controller;

            if (! class_exists($controller)) {
                $this->error(sprintf('%s controller does not exists', $controller));
                exit();
            }
        }

        return $controller;
    }

    private function getModel($model)
    {
        if (! class_exists($model)) {
            $namespace = config('laracrud.model.namespace');
            $namespace = NamespaceResolver::getFullNS($namespace);
            $model = rtrim($namespace, '\\') . '\\' . $model;
        }

        if (! class_exists($model)) {
            $this->error(sprintf('%s model does not exists', $model));
            exit();
        }

        return $model;
    }
}
