<?php

namespace LaraCrud\Console\ReactJs;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use LaraCrud\Crud\ReactJs\ReactJsModelCrud;
use LaraCrud\Helpers\Helper;
use LaraCrud\Services\ScanDirectoryService;

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
    protected $description = 'Model generator based on folder or class';

    /**
     * @throws \ReflectionException
     */
    public function handle()
    {
        try {
            $modelClass = $this->argument('model');
            if ($model = $this->checkModelExists($modelClass)) {
                $reactModelCrud = new ReactJsModelCrud($model);
                $reactModelCrud->save();
                $this->info(sprintf('%s model created successfully', $modelClass));
            } else {
                $path = $this->toPath($modelClass);
                $fullPath = base_path($path);
                $scan = new ScanDirectoryService($fullPath);
                $files = $scan->scan();
                $s = 0;
                foreach ($files as $file) {
                    try {
                        $fullClass = $modelClass . '\\' . pathinfo((string) $file, PATHINFO_FILENAME);

                        if (class_exists($fullClass) && is_subclass_of($fullClass, Model::class)) {
                            $enumCrud = new ReactJsModelCrud(new $fullClass());
                            $enumCrud->save();
                            $this->info(sprintf('%s file created successfully', $fullClass));
                            ++$s;
                        }
                    } catch (\Exception $e) {
                        $this->warn($e->getMessage());
                    }
                }
                $this->info(sprintf('%d model class created out of %d', $s, count($files)));
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     *  Check if Model or Parent Model exists . If so then create object from them otherwise return warning and exit.
     */
    private function checkModelExists(mixed $model)
    {
        $modelFullName = $this->modelFullName($model);
        if (class_exists($modelFullName)) {
            return new $modelFullName();
        } else {
            return false;
        }
    }

    /**
     * @param $model
     */
    private function modelFullName($model): false|string
    {
        $modelNamespace = $this->getFullNS(config('laracrud.model.namespace', 'App'));
        if (! class_exists($model)) {
            return $modelNamespace . '\\' . $model;
        }

        return false;
    }
}
