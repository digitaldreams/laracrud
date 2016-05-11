<?php

namespace App\Libs\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class Route extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tb:route {controller}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create routes based on Controller class';

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
            $controller = $this->argument('controller');

            if (!stripos("App\Http\Controllers\\", $controller)) {
                $controller = 'App\Http\Controllers\\' . $controller;
            }


            $modelCrud = new \App\Libs\RouteCrud($controller);
            $modelCrud->make();
            $this->info('Routes created successfully');
        } catch (\Exception $ex) {
            $this->error($ex->getMessage());
        }
    }

}
