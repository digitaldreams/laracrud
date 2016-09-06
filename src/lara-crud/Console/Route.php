<?php

namespace LaraCrud\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class Route extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laracrud:route {controller}';

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
            $controllers = [];
            $controller = $this->argument('controller');
            if ($controller == 'all') {

                $dirIt = new \RecursiveDirectoryIterator(app_path('Http/Controllers'));
                $rit = new \RecursiveIteratorIterator($dirIt);

                while ($rit->valid()) {

                    if (!$rit->isDot()) {
                        $controllers[] = "App\Http\Controllers\\" . str_replace(".php","", $rit->getSubPathName());
                    }
                    $rit->next();
                }
                $routeCrud = new \App\Libs\RouteCrud($controllers);
            } else {
                if (!stripos("App\Http\Controllers\\", $controller)) {
                    $controller = 'App\Http\Controllers\\' . $controller;
                }


                $routeCrud = new \App\Libs\RouteCrud($controller);
            }

            $routeCrud->make();
            $this->info('Routes created successfully');
        } catch (\Exception $ex) {
            $this->error($ex->getMessage());
        }
    }

}
