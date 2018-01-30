<?php

namespace LaraCrud\Console;

use Illuminate\Console\Command;
use LaraCrud\Crud\RouteCrud;
use LaraCrud\Helpers\Helper;

class Route extends Command
{
    use Helper;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laracrud:route {controller} {--api}';

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
            $controller = $this->argument('controller');
            $api = $this->option('api');
            $namespace = $api == true ? config('laracrud.controller.apiNamespace') : config('laracrud.controller.namespace');
            $namespace = $this->getFullNS($namespace);

            if ($controller == 'all') {
                $path = $this->toPath($namespace);
                $dirIt = new \RecursiveDirectoryIterator(base_path($path));
                $rit = new \RecursiveIteratorIterator($dirIt);

                while ($rit->valid()) {

                    if (!$rit->isDot()) {
                        $controllers[] = rtrim($namespace, "\\") . "\\" . str_replace(".php",
                                "", str_replace("/", "\\", $rit->getSubPathName()));
                    }
                    $rit->next();
                }
                $routeCrud = new RouteCrud($controllers, $api);
            } else {
                $controller = str_replace("/", "\\", $controller);
                if (!stripos(rtrim($namespace, "\\") . "\\", $controller)) {
                    $controller = rtrim($namespace, "\\") . '\\' . $controller;
                }

                $routeCrud = new RouteCrud($controller, $api);
            }

            $routeCrud->save();
            if (!empty($routeCrud->errors)) {
                $this->error(implode(", ", $routeCrud->errors));
            } else {
                $this->info('Routes created successfully');
            }
        } catch (\Exception $ex) {
            $this->error($ex->getMessage() . ' on line ' . $ex->getLine() . ' in ' . $ex->getFile());
        }
    }
}