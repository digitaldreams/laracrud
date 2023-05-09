<?php

namespace LaraCrud\Console;

use Illuminate\Console\Command;
use LaraCrud\Crud\ModelFactory;
use LaraCrud\Helpers\Helper;

class Factory extends Command
{
    use Helper;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laracrud:factory 
    {model : Name of the Eloquent Model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a Model Factory class based on Model';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $model = $this->argument('model');
            $modelNamespace = $this->getFullNS(config('laracrud.model.namespace', 'App'));
            if (! class_exists($model)) {
                $model = $modelNamespace . '\\' . $model;
            }
            if (! class_exists($model)) {
                throw new \Exception('Model ' . $model . ' is not exists');
            }

            $factoryCrud = new ModelFactory(new $model());
            $factoryCrud->save();
            $this->info('Factory class created successfully');
        } catch (\Exception $ex) {
            $this->error($ex->getMessage() . ' on line ' . $ex->getLine() . ' in ' . $ex->getFile());
        }
    }
}
