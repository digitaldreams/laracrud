<?php

namespace LaraCrud\Console;

use DbReader\Table as TableReader;
use Illuminate\Console\Command;
use LaraCrud\Crud\Controller;
use LaraCrud\Crud\Model;
use LaraCrud\Crud\Request;
use LaraCrud\Crud\RequestResource;
use LaraCrud\View\Create;
use LaraCrud\View\Edit;
use LaraCrud\View\Index;
use LaraCrud\View\Show;

class Mvc extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laracrud:mvc {table} {--api}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Request, Model, Controller, View based on table';

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

            $table = $this->argument('table');
            $api = $this->option('api');
            Request::checkMissingTable($table);
            try {
                $modelCrud = new Model($table);
                $modelCrud->save();
                $modelNs = $modelCrud->getFullModelName();
                $model = new $modelNs;
                $this->info('Model class created successfully');

            } catch (\Exception $e) {
                $this->error($e->getMessage());
                exit();
            }

            try {
                $requestCrud = new RequestResource($model, false, $api);
                $requestCrud->save();
                $this->info('Request classes created successfully');
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }


            try {
                $controllerCrud = new Controller($modelNs, false, false, $api);
                $controllerCrud->save();
                $this->info('Controller class created successfully');
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }


            if ($api) {
                $this->info('API resources created successfully');
                return;
            }

            $this->warn('Creating views files');
            try {
                $indexPage = new Index($model);
                $indexPage->save();
                $this->info('Index page created successfully');

                $showPage = new Show($model);
                $showPage->save();
                $this->info('Show page created successfully');

                $createPage = new Create($model);
                $createPage->save();
                $this->info('Create page created successfully');

                $edit = new Edit($model);
                $edit->save();
                $this->info('Edit page created successfully');
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }

        } catch (\Exception $ex) {
            $this->error($ex->getMessage() . ' on line ' . $ex->getLine() . ' in ' . $ex->getFile());
        }
    }
}