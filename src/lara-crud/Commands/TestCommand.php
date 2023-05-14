<?php

namespace LaraCrud\Commands;

use Illuminate\Console\Command;
use LaraCrud\Generators\Test as TestCrud;
use LaraCrud\Helpers\Helper;
use LaraCrud\Repositories\TestRepository;

class TestCommand extends Command
{
    use Helper;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laracrud:test
        {controller : Controller Name}
        {model : Model name}
        {--f|name= : Test Class Name}
        {--p|parent= : Generate a nested resource controller class. Give the Parent Eloquent Model name. e.g --parent=Post}
        {--api : Whether its an API Controller Test}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create test based on Controller class';

    protected string $fileName = '';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $testCrud = new TestCrud($this->initTestRepository(), $this->fileName);

            $testCrud->save();

            $this->info(sprintf('%s Test created successfully', $this->fileName));
        } catch (\Exception $ex) {
            $this->error($ex->getMessage() . ' on line ' . $ex->getLine() . ' in ' . $ex->getFile());
        }
    }

    private function initTestRepository(): TestRepository
    {
        $controller = $this->argument('controller');
        $model = $this->argument('model');
        $parent = $this->option('parent');
        $fileName = $this->option('name');
        $api = $this->option('api');

        if (! class_exists($controller)) {
            $namespace = true == $api ? config('laracrud.controller.apiNamespace') : config('laracrud.controller.namespace');
            $namespace = $this->getFullNS($namespace);
            $controller = rtrim($namespace, '\\') . '\\' . $controller;
        }

        if (! class_exists($controller)) {
            $this->error(sprintf('%s controller does not exists', $controller));
            exit();
        }


        if (! class_exists($model)) {
            $namespace = config('laracrud.model.namespace');
            $namespace = $this->getFullNS($namespace);
            $model = rtrim($namespace, '\\') . '\\' . $model;
        }

        if (! class_exists($model)) {
            $this->error(sprintf('%s model does not exists', $model));
            exit();
        }
        if (! empty($parent)) {
            if (! class_exists($parent)) {
                $namespace = config('laracrud.model.namespace');
                $namespace = $this->getFullNS($namespace);
                $parent = rtrim($namespace, '\\') . '\\' . $parent;

                if (! class_exists($parent)) {
                    $this->error(sprintf('%s model does not exists', $parent));
                    exit();
                }
                $parent = new $parent();
            }
        }

        $this->fileName = empty($fileName) ? (new \ReflectionClass($model))->getShortName() . 'Test' : $fileName;

        return new TestRepository($controller, new $model(), $parent, $api);
    }
}
