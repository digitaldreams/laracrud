<?php

namespace LaraCrud\Command\ReactJs;

use Illuminate\Console\Command;
use LaraCrud\Crud\ReactJs\ReactJsFormCrud;
use LaraCrud\Helpers\Helper;

class FormCommand extends Command
{
    use Helper;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reactjs:form {model} {controller}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enum generator based on folder or class';

    public function handle()
    {
        try {
            $model = $this->getModal($this->argument('model'));
            $controller = $this->getController($this->argument('controller'));
            $formCrud = new ReactJsFormCrud($model, $controller);
            $formCrud->save();
            $this->info('Form component generated successfully');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     *  Check if Model or Parent Model exists . If so then create object from them otherwise return warning and exit.
     */
    private function getModal(mixed $model)
    {
        if (class_exists($model)) {
            return new $model();
        }

        $namespace = $this->getFullNS(config('laracrud.model.namespace', 'App'));
        $model = rtrim($namespace, '\\') . '\\' . $model;

        return new $model();
    }

    protected function getController(string $controller)
    {
        if (! class_exists($controller)) {
            $namespace = config('laracrud.controller.apiNamespace');
            $namespace = $this->getFullNS($namespace);
            $controller = rtrim($namespace, '\\') . '\\' . $controller;
        }

        return app()->make($controller);
    }
}
