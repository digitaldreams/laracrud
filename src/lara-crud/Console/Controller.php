<?php
/**
 * Created by PhpStorm.
 * User: Tuhin
 * Date: 9/10/2017
 * Time: 5:37 PM
 */

namespace LaraCrud\Console;

use Illuminate\Console\Command;
use LaraCrud\Crud\Controller as ControllerCrud;


class Controller extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "laracrud:controller {model} {name?} {--only=} {--api}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a Controller based on Model';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $model = $this->argument('model');
            $name = $this->argument('name');
            $only = $this->option('only');
            $api = $this->option('api');
            $onlyArr = !empty($only) ? explode(",", $only) : '';
            $controllerCrud = new ControllerCrud($model, $name, $onlyArr, $api);
            $controllerCrud->save();
            $this->info('Controller class successfully created');
        } catch (\Exception $ex) {
            $this->error($ex->getMessage() . ' on line ' . $ex->getLine() . ' in ' . $ex->getFile());
        }
    }
}