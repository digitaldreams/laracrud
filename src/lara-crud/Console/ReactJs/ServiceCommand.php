<?php

namespace LaraCrud\Console\ReactJs;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use LaraCrud\Crud\ReactJs\ReactJsServiceCrud;
use LaraCrud\Helpers\Helper;
use LaraCrud\Services\ScanDirectoryService;

class ServiceCommand extends Command
{
    use Helper;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reactjs:service {controller}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enum generator based on folder or class';

    public function handle()
    {
        try {
            $controller = $this->argument('controller');

            if ($apiEndpointCrud = $this->initReactJsApiService($controller)) {
                $apiEndpointCrud->save();
                $this->info(sprintf('%s Api service file generated successfully', $controller));
            } else {
                $path = $this->toPath($controller);
                $fullPath = base_path($path);
                $scan = new ScanDirectoryService($fullPath);
                $files = $scan->scan();
                $s = 0;
                foreach ($files as $file) {
                    try {
                        $fullClass = $controller . '\\' . pathinfo($file, PATHINFO_FILENAME);

                        if (class_exists($fullClass)) {
                            $enumCrud = new ReactJsServiceCrud($fullClass);
                            $enumCrud->save();
                            $this->info(sprintf('%s file created successfully', $fullClass));
                            ++$s;
                        }
                    } catch (\Exception $e) {
                        $this->warn($e->getMessage());
                        continue;
                    }
                }
                $this->info(sprintf('%d api service class created out of %d', $s, count($files)));
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            Log::error($e->getTraceAsString());
        }
    }

    /**
     * @param mixed $controller
     *
     * @throws \ReflectionException
     */
    protected function initReactJsApiService(string $controller): ReactJsServiceCrud|bool
    {
        if (! class_exists($controller)) {
            $namespace = config('laracrud.controller.apiNamespace');
            $namespace = $this->getFullNS($namespace);
            $controller = rtrim($namespace, '\\') . '\\' . $controller;
        }

        if (! class_exists($controller)) {
            return false;
        }

        return new ReactJsServiceCrud($controller);
    }
}
