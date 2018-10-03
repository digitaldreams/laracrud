<?php

namespace LaraCrud\Console;

use Illuminate\Console\Command;

class Factory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "laracrud:factory {model} {--name=}";

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
            $name = $this->option('name');

            $factoryCrud = new \LaraCrud\Crud\ModelFactory($model, $name);
            $factoryCrud->save();
            $this->info('Factory class created successfully');
        } catch (\Exception $ex) {
            $this->error($ex->getMessage() . ' on line ' . $ex->getLine() . ' in ' . $ex->getFile());
        }
    }
}