<?php

namespace LaraCrud\Command\ReactJs;

use Illuminate\Console\Command;
use LaraCrud\Crud\ReactJs\ReactJsApiEndpointCrud;
use LaraCrud\Crud\ReactJs\ReactJsEnumCrud;
use LaraCrud\Helpers\Helper;
use LaraCrud\Services\ScanDirectoryService;

class ApiEndpointCommand extends Command
{
    use Helper;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reactjs:apiEndpoint {controller}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ApiEndpoint generator based on controller Class';

    public function handle()
    {
        try {
            $controller = $this->argument('controller');

            if ($apiEndpointCrud = $this->initReactJsApiEndpoint($controller)) {
                $apiEndpointCrud->save();
                $this->info(sprintf('%s apiEndpoint file generated successfully', $controller));
            } else {
                $path = $this->toPath($controller);
                $fullPath = base_path($path);
                $scan = new ScanDirectoryService($fullPath);
                $files = $scan->scan();
                $s = 0;
                foreach ($files as $file) {
                    try {
                        $fullClass = $controller . '\\' . pathinfo((string) $file, PATHINFO_FILENAME);

                        if (class_exists($fullClass)) {
                            $enumCrud = new ReactJsApiEndpointCrud($fullClass);
                            $enumCrud->save();
                            $this->info(sprintf('%s file created successfully', $fullClass));
                            ++$s;
                        }
                    } catch (\Exception $e) {
                        $this->warn($e->getMessage());
                    }
                }
                $this->info(sprintf('%d api endpoints class created out of %d', $s, count($files)));
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    protected function initReactJsApiEndpoint(string $controller): ReactJsApiEndpointCrud|bool
    {
        if (! class_exists($controller)) {
            $namespace = config('laracrud.controller.apiNamespace');
            $namespace = $this->getFullNS($namespace);
            $controller = rtrim($namespace, '\\') . '\\' . $controller;
        }

        if (! class_exists($controller)) {
            return false;
        }

        return new ReactJsApiEndpointCrud($controller);
    }
}
