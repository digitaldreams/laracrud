<?php

namespace LaraCrud\Commands;

use DbReader\Table as TableReader;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\Facades\Gate;
use LaraCrud\Generators\ViewController;
use LaraCrud\Helpers\Helper;
use LaraCrud\View\Create;
use LaraCrud\View\Edit;
use LaraCrud\View\Index;
use LaraCrud\View\Page;
use LaraCrud\View\Partial\Form;
use LaraCrud\View\Partial\Modal;
use LaraCrud\View\Partial\Panel;
use LaraCrud\View\Partial\Table;
use LaraCrud\View\Show;

class ViewCommand extends Command
{
    use Helper;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laracrud:view
        {model : Eloquent Model Name. E.g. Post}
        {--p|page= : Any of this (index|create|edit|show|form|table|panel|modal). Ignore this option will create a complete CRUD views.}
        {--name= : Name of the view file.}
        {--c|controller= : Create view files by reading Public GET method response}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create view based on Model';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $model = $this->argument('model');
            $page = $this->option('page');
            $type = $this->option('type');
            $name = $this->option('name');
            $controller = $this->option('controller');

            Page::$controller = $this->getControllerNs($controller);
            Page::fetchRoute();
            $modelFulNs = $this->getModelFullNs($model);

            if (!class_exists($modelFulNs)) {
                $this->error($model . ' does not exists');

                return false;
            }
            $modelObj = new $modelFulNs();
            $policies = Gate::policies();
            Page::$policy = $policies[$modelFulNs] ?? false;

            $tableReader = new TableReader($modelObj->getTable());
            if (!empty($page)) {
                $pageMaker = $this->pageMaker($page, $modelObj, $name, $type);
                if (!empty($pageMaker)) {
                    $pageMaker->save();
                    $this->info($page . ' page created successfully');
                }
            } elseif (!empty($controller)) {
                $controllerFullNs = $this->getControllerNs($controller);

                if (class_exists($controllerFullNs)) {
                    $viewController = new ViewController($controllerFullNs, $tableReader);
                    $viewController->save();

                    if ((is_countable($viewController->errors) ? count($viewController->errors) : 0) > 0) {
                        $this->error(implode("\n", $viewController->errors));
                    } else {
                        $this->info('Controller views saved successfully');
                    }
                }
            } else {
                $indexPage = new Index($modelObj, $name, $type);
                $indexPage->save();
                $this->info('Index page created successfully');

                $showPage = new Show($modelObj, $name, $type);
                $showPage->save();
                $this->info('Show page created successfully');

                $createPage = new Create($modelObj, $name);
                $createPage->save();
                $this->info('Create page created successfully');

                $edit = new Edit($modelObj, $name);
                $edit->save();
                $this->info('Edit page created successfully');
            }
        } catch (\Exception $ex) {
            $this->error($ex->getMessage() . ' on line ' . $ex->getLine() . ' in ' . $ex->getFile());
        }
    }

    /**
     * @param $page
     * @param string        $name
     * @param string        $type
     *
     */
    private function pageMaker($page, EloquentModel $eloquentModel, $name = '', $type = ''): bool|\LaraCrud\View\Partial\Form|\LaraCrud\View\Partial\Modal|\LaraCrud\View\Partial\Panel|\LaraCrud\View\Partial\Table
    {
        $pageMaker = match ($page) {
            'form' => new Form($eloquentModel, $name),
            'table' => new Table($eloquentModel, $name),
            'modal' => new Modal($eloquentModel, $name),
            'panel' => new Panel($eloquentModel, $name),
            'create' => new Create($eloquentModel, $name),
            'edit' => new Edit($eloquentModel, $name),
            'show' => new Show($eloquentModel, $name, $type),
            'index' => new Index($eloquentModel, $name, $type),
            default => false,
        };

        return $pageMaker;
    }

    /**
     * @param $controller
     *
     * @return string
     */
    protected function getControllerNs($controller)
    {
        $namespace = config('laracrud.controller.namespace');

        return $this->getFullNamespace($namespace, $controller);
    }

    private function getFullNamespace($namespace, $class)
    {
        if (class_exists($class)) {
            return $class;
        }
        $fullNs = $this->getFullNS(rtrim((string) $namespace, '\\') . '\\' . $class);
        if (class_exists($fullNs)) {
            return $fullNs;
        }

        return false;
    }

    private function getModelFullNs($model = '')
    {
        if (empty($model)) {
            return false;
        }

        if (!class_exists($model)) {
            $modelNS = $this->getFullNS(config('laracrud.model.namespace'));

            return $fullClass = $modelNS . '\\' . $model;
        }

        return $model;
    }
}
