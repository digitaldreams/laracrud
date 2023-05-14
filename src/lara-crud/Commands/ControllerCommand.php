<?php

/**
 * Created by PhpStorm.
 * User: Tuhin
 * Date: 9/10/2017
 * Time: 5:37 PM.
 */

namespace LaraCrud\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use LaraCrud\Generators\Controller as ControllerCrud;
use LaraCrud\Generators\Policy;
use LaraCrud\Generators\RequestResource as RequestResourceCrud;
use LaraCrud\Helpers\Helper;
use LaraCrud\Helpers\NamespaceResolver;
use LaraCrud\Repositories\ControllerRepository;
use Illuminate\Database\Eloquent\Model;

class ControllerCommand extends Command
{
    use Helper;

    protected Model $model;

    protected ?Model $parent=null;

    /**
     * @var string[][]
     */
    protected array $methods = [
        'web' => [
            'index',
            'show',
            'create',
            'store',
            'edit',
            'update',
            'destroy',
        ],
        'api' => [
            'index',
            'show',
            'store',
            'update',
            'destroy',
        ],
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laracrud:controller
      {model : Name of the Eloquent Model.}
      {name? : Custom Controller Name}
      {--o|only= : If you want to create partial resourceful controller. e.g. --only=index,show}
      {--api : whether its an API controller or now}
      {--p|parent= : Generate a nested resource controller class. Give the Parent Eloquent Model name. e.g --parent=Post}
      {--w|with= : Create Custom Request Classes or Policy along with Newly created Controller. e.g --with=request,policy }';

    /**
     * The console command description.
     *
     */
    protected $description = 'Create a Controller based on Model';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $this->checkModelExists();

            $name = $this->argument('name');
            $api = (bool)$this->option('api');
            $with = $this->option('with');

            $withArr = !empty($with) ? explode(',', $with) : [];

            if (in_array('request', $withArr)) {
                $this->createRequestResource($api);
            }
            $controllerRepository = $this->initControllerCrud($api);

            $controllerCrud = new ControllerCrud($controllerRepository, $this->model, $name, $api);
            $controllerCrud->save();
            $this->info(sprintf('%s  class successfully created', $controllerCrud->getFullName()));

            if (in_array('policy', $withArr)) {
                $policyCrud = new Policy($this->model, $controllerCrud->getFullName());
                $policyCrud->save();
                $this->info('Policy class created successfully');
            }
        } catch (\Exception $ex) {
            Log::error($ex->getTraceAsString());
            $this->error(sprintf('%s on line %s  in %s', $ex->getMessage(), $ex->getLine(), $ex->getFile()));

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }


    /**
     *  Check if Model or Parent Model exists . If so then create object from them otherwise return warning and exit.
     */
    private function checkModelExists()
    {
        $model = $this->argument('model');
        $modelFullName = NamespaceResolver::modelFullName($model);
        if (class_exists($modelFullName)) {
            $this->model = new $modelFullName();
        } else {
            $this->error(sprintf('%s model does not exists in %s.', $model, $modelFullName));
            exit();
        }
        $parent = $this->option('parent');

        if (!empty($parent)) {
            $parentModelFullName = NamespaceResolver::modelFullName($parent);

            if (class_exists($parentModelFullName)) {
                $this->parent = new $parentModelFullName();
            } else {
                $this->error(sprintf('parent model %s does not exists in %s.', $parent, $parentModelFullName));
                exit();
            }
        }
    }

    private function createRequestResource(bool $api = false)
    {
        $requestResource = new RequestResourceCrud($this->model, false, $api);

        $requestResource->save();

        $this->info('Request controller classes created successfully');
    }

    protected function initControllerCrud($api): ControllerRepository
    {
        $only = $this->option('only');
        if (!empty($only)) {
            $methods = explode(',', $only);
        } else {
            $methods = $api == true ? $this->methods['api'] : $this->methods['web'];
            if ($this->isSoftDeleteAble()) {
                $methods[] = 'restore';
                $methods[] = 'forceDelete';
            }
        }
        $cr = new ControllerRepository();

        if ($api) {
            $cr->setApi();
        } else {
            $cr->setWeb();
        }

        return $cr->addMethodsFromString($methods, $this->model, $this->parent);
    }


    /**
     * Whether given model implement SoftDeletes trait.
     * If so then we have to add restore and forceDelete methods as well.
     */
    private function isSoftDeleteAble(): bool
    {
        return in_array(SoftDeletes::class, class_uses($this->model));
    }
}
