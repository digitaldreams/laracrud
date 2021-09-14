<?php

namespace LaraCrud\Console\ReactJs;

use Illuminate\Console\Command;
use LaraCrud\Crud\ReactJs\ReactJsModelCrud;
use LaraCrud\Helpers\Helper;

class ModelCommand extends Command
{
    use Helper;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reactjs:model {model}';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Model Generator based on Folder or Class';

    /**
     * @throws \ReflectionException
     */
    public function handle()
    {
        try {
            $model = $this->checkModelExists();
            $reactModelCrud = new ReactJsModelCrud($model);
            $reactModelCrud->save();
            $this->info('Thanks for having me');
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
            return new $modelFullName();
        } else {
            $this->error(sprintf('%s model does not exists in %s.', $model, $modelFullName));
            exit();
        }


    }

    /**
     * @param $model
     *
     * @return false|string
     */
    private function modelFullName($model)
    {
        $modelNamespace = $this->getFullNS(config('laracrud.model.namespace', 'App'));
        if (! class_exists($model)) {
            return $modelNamespace . '\\' . $model;
        }

        return false;
    }

}
