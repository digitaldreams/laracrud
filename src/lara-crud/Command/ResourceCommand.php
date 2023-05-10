<?php

namespace LaraCrud\Command;

use Illuminate\Console\Command;
use LaraCrud\Crud\ApiResource;
use LaraCrud\Helpers\Helper;

class ResourceCommand extends Command
{
    use Helper;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laracrud:resource
        {model : Eloquent Model name}
        {name? : Custom name of your Resource. e.g. PostResource }
        {--c|createAll : create related Resource classes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a API resource class based on Model';

    private \Illuminate\Database\Eloquent\Model $model;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $this->checkModelExists();
            $crud = new ApiResource($this->model);
            $crud->save();
            $this->info(sprintf('%s created successfully on %s', $crud->modelName, $crud->getFullName()));
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     *  Check if Model or Parent Model exists . If so then create object from them otherwise return warning and exit.
     */
    private function checkModelExists()
    {
        $model = $this->argument('model');
        $modelFullName = $this->modelFullName($model);
        if (class_exists($modelFullName)) {
            $this->model = new $modelFullName();
        } else {
            $this->error(sprintf('%s model does not exists in %s.', $model, $modelFullName));
            exit();
        }
    }

    /**
     * @param $model
     */
    private function modelFullName($model): false|string
    {
        $modelNamespace = $this->getFullNS(config('laracrud.model.namespace', 'App'));
        if (!class_exists($model)) {
            return $modelNamespace . '\\' . $model;
        }

        return false;
    }
}
