<?php

namespace App\Libs\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class Mvc extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tb:mvc {table}';

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
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        try {
            $table = $this->argument('table');
            $modelCrud = new \App\Libs\ModelCrud($table);
            $modelCrud->make();

            $requestCrud = new \App\Libs\RequestCrud($table);
            $requestCrud->make();

            $modelName = $modelCrud->getFullModelName($table);
            $controllerCrud = new \App\Libs\ControllerCrud($modelName);
            $controllerCrud->make();

            $viewCrud = new \App\Libs\ViewCrud($table);
            $viewCrud->make();
            $this->info('View created successfully');
        } catch (\Exception $ex) {
            $this->error($ex->getMessage());
        }
    }

}
