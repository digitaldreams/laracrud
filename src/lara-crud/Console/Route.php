<?php

namespace LaraCrud\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use LaraCrud\Crud\RouteCrud;

class Route extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laracrud:route {controller} {--template=web}';

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
            $controllers = [];
            $controller  = $this->argument('controller');
            $template = $this->option('template');

            if ($controller == 'all') {

                $dirIt = new \RecursiveDirectoryIterator(app_path('Http/Controllers'));
                $rit   = new \RecursiveIteratorIterator($dirIt);

                while ($rit->valid()) {

                    if (!$rit->isDot()) {
                        $controllers[] = "App\Http\Controllers\\".str_replace(".php",
                                "", $rit->getSubPathName());
                    }
                    $rit->next();
                }
                $routeCrud = new RouteCrud($controllers);
            } else {
                $controller = str_replace("/", "\\", $controller);
                if (!stripos("App\Http\Controllers\\", $controller)) {
                    $controller = 'App\Http\Controllers\\'.$controller;
                }


                $routeCrud = new RouteCrud($controller, $template);
            }

            $routeCrud->save();
            if (!empty($routeCrud->errors)) {
                $this->error(implode(", ", $routeCrud->errors));
            } else {
                $this->info('Routes created successfully');
            }
        } catch (\Exception $ex) {
            $this->error($ex->getMessage().' on line '.$ex->getLine().' in '.$ex->getFile());
        }
    }
}