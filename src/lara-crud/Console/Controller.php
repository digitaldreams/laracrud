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
use LaraCrud\Crud\Policy;
use LaraCrud\Crud\RequestController as RequestControllerCrud;
use LaraCrud\Helpers\Helper;
use LaraCrud\Crud\RequestResource as RequestResourceCrud;


class Controller extends Command
{
    use Helper;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "laracrud:controller {model} {name?} {--only=} {--api} {--parent=} {--with=}";

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
            $with = $this->option('with');
            $withArr = !empty($with) ? explode(",", $with) : [];
            $onlyArr = !empty($only) ? explode(",", $only) : '';
            $parent = $this->option('parent');

            if (in_array("request", $withArr)) {
                $modelFullName = $this->modelFullName($model);
                if (class_exists($modelFullName)) {
                    $modelObj = new $modelFullName;
                    $requestResource = new RequestResourceCrud($modelObj->getTable(), false, $api);
                    $requestResource->setModel($modelFullName);
                    $requestResource->save();
                    $this->info('Request controller classes created successfully');
                }
            }

            $controllerCrud = new ControllerCrud($model, $name, $onlyArr, $api, $parent);
            $controllerCrud->save();
            $this->info('Controller class successfully created');

            if (in_array("policy", $withArr)) {
                $policyCrud = new Policy($model, $controllerCrud->getFullName());
                $policyCrud->save();
                $this->info('Policy class created successfully');
            }
        } catch (\Exception $ex) {
            $this->error($ex->getMessage() . ' on line ' . $ex->getLine() . ' in ' . $ex->getFile());
        }
    }

    private function modelFullName($model)
    {
        $modelNamespace = $this->getFullNS(config('laracrud.model.namespace', 'App'));
        if (!class_exists($model)) {
            return $modelNamespace . '\\' . $model;
        }
        return false;
    }
}