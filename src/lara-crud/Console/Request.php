<?php

namespace LaraCrud\Console;

use Illuminate\Console\Command;
use LaraCrud\Crud\Request as RequestCrud;
use LaraCrud\Crud\RequestController as RequestControllerCrud;
use LaraCrud\Crud\RequestResource as RequestResourceCrud;

class Request extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laracrud:request {table} {name?} {--controller=} {--resource=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a request class based on table';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $table = $this->argument('table');
            $name = $this->argument('name');
            $controller = $this->option('controller');
            $resource = $this->option('resource');
            if (!empty($controller)) {
                $requestController = new RequestControllerCrud($table, $controller);
                $requestController->save();
                $this->info('Request controller classes created successfully');

            } elseif (!empty($resource)) {
                $methods = explode(",", $resource);
                $requestResource = new RequestResourceCrud($table, $methods);
                $requestResource->save();
                $this->info('Request resource classes created successfully');

            } else {
                if (strripos($table, ",")) {
                    $table = explode(",", $table);
                    RequestCrud::checkMissingTable($table);
                    foreach ($table as $tb) {
                        $requestCrud = new RequestCrud($tb);
                        $requestCrud->save();
                    }
                } else {
                    $requestCrud = new RequestCrud($table, $name);
                    $requestCrud->save();
                }
                $this->info('Request class created successfully');
            }


        } catch (\Exception $ex) {
            $this->error($ex->getMessage() . ' on line ' . $ex->getLine() . ' in ' . $ex->getFile());
        }
    }
}