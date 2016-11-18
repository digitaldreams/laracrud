<?php

namespace LaraCrud\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class Mvc extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laracrud:mvc {table}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create view based on table';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $table     = $this->argument('table');
            \LaraCrud\LaraCrud::checkMissingTable($table);
            $modelCrud = new \LaraCrud\ModelCrud($table);
            $modelCrud->make();

            $requestCrud = new \LaraCrud\RequestCrud($table);
            $requestCrud->make();

            $modelName      = $modelCrud->getModelName($table);
            $controllerCrud = new \LaraCrud\ControllerCrud($modelName);
            $controllerCrud->make();

            $viewCrud = new \LaraCrud\ViewCrud($table);
            $viewCrud->make();

          

            $this->info('Model, View, Request and Controlleer successfully created ');
        } catch (\Exception $ex) {
            $this->error($ex->getMessage().' on line '.$ex->getLine().' in '.$ex->getFile());
        }
    }
}