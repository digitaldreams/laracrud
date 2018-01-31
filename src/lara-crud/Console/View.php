<?php

namespace LaraCrud\Console;

use DbReader\Table as TableReader;
use Illuminate\Console\Command;
use LaraCrud\Crud\Model;
use LaraCrud\Crud\ViewController;
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

class View extends Command
{
    use Helper;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laracrud:view {table} {--page=} {--type=} {--name=} {--controller=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create view based on table';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $table = $this->argument('table');
            $page = $this->option('page');
            $type = $this->option('type');
            $name = $this->option('name');
            $controller = $this->option('controller');

            Page::$controller = $this->getControllerNs($controller);
            Page::fetchRoute();

            $tables = explode(",", $table);
            foreach ($tables as $tb) {
                Model::checkMissingTable($tb);
                $tableReader = new TableReader($tb);
                if (!empty($page)) {
                    $pageMaker = $this->pageMaker($page, $tableReader, $name, $type);
                    if (!empty($pageMaker)) {
                        $pageMaker->save();
                        $this->info($page . ' page created successfully');
                    }
                } elseif (!empty($controller)) {
                    $controllerFullNs = $this->getControllerNs($controller);

                    if (class_exists($controllerFullNs)) {
                        $viewController = new ViewController($controllerFullNs, $tableReader);
                        $viewController->save();

                        if (count($viewController->errors) > 0) {
                            $this->error(implode("\n", $viewController->errors));
                        }else{
                            $this->info('Controller views saved successfully');
                        }
                    }
                } else {
                    $indexPage = new Index($tableReader, $name, $type);
                    $indexPage->save();
                    $this->info('Index page created successfully');

                    $showPage = new Show($tableReader, $name, $type);
                    $showPage->save();
                    $this->info('Show page created successfully');

                    $createPage = new Create($tableReader, $name);
                    $createPage->save();
                    $this->info('Create page created successfully');

                    $edit = new Edit($tableReader, $name);
                    $edit->save();
                    $this->info('Edit page created successfully');

                }
            }

        } catch (\Exception $ex) {
            $this->error($ex->getMessage() . ' on line ' . $ex->getLine() . ' in ' . $ex->getFile());
        }
    }

    /**
     * @param $page
     * @param TableReader $tableReader
     * @param string $name
     * @param string $type
     * @return bool|Form|Modal|Panel|Table
     */
    private function pageMaker($page, TableReader $tableReader, $name = '', $type = '')
    {
        switch ($page) {
            case 'form':
                $pageMaker = new Form($tableReader, $name);
                break;
            case 'table':
                $pageMaker = new Table($tableReader, $name);
                break;
            case 'modal':
                $pageMaker = new Modal($tableReader, $name);
                break;
            case 'panel':
                $pageMaker = new Panel($tableReader, $name);
                break;
            case 'create':
                $pageMaker = new Create($tableReader, $name);
                break;
            case 'edit':
                $pageMaker = new Edit($tableReader, $name);
                break;
            case 'show':
                $pageMaker = new Show($tableReader, $name, $type);
                break;
            case 'index':
                $pageMaker = new Index($tableReader, $name, $type);
                break;
            default:
                $pageMaker = false;
                break;
        }
        return $pageMaker;
    }

    /**
     * @param $controller
     * @return string
     */
    protected function getControllerNs($controller)
    {
        $namespace = config('laracrud.controller.namespace');
        $fullNs = $this->getFullNS(rtrim($namespace, "\\") . "\\" . $controller);

        if (class_exists($controller)) {
            return $controller;
        }
        if (class_exists($fullNs)) {
            return $fullNs;
        }
        return false;
    }
}