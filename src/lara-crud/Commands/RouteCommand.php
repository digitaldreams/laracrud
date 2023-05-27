<?php

namespace LaraCrud\Commands;

use Illuminate\Console\Command;
use LaraCrud\Generators\RouteCrud;
use LaraCrud\Helpers\Helper;
use LaraCrud\Helpers\NamespaceResolver;
use PhpParser\ParserFactory;
use PhpParser\Error;
use PhpParser\NodeDumper;

class RouteCommand extends Command
{
    use Helper;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laracrud:route
        {controller : Controller name}
        {--api : Whether its an API controller}';

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
            $controller = $this->argument('controller');
            $api = (bool)$this->option('api');
            $namespace = NamespaceResolver::getControllerRoot($api);
            $namespace = NamespaceResolver::getFullNS($namespace);

            $controller = str_replace('/', '\\', $controller);
            if (!stripos(rtrim($namespace, '\\') . '\\', (string)$controller)) {
                $controller = rtrim($namespace, '\\') . '\\' . $controller;
            }
            if(!class_exists($controller)){
                throw new \Exception(sprintf('Controller %s not found in %s',$controller,$namespace));
            }

            $routeCrud = new RouteCrud($controller, $api);

            $routeCrud->save();
            if (!empty($routeCrud->errors)) {
                $this->error(implode(', ', $routeCrud->errors));
            } else {
                $this->info('Routes created successfully');
            }
        } catch (\Exception $ex) {
            $this->error(sprintf('%s on line %d in %s', $ex->getMessage(), $ex->getLine(), $ex->getFile()));
        }
    }
}
