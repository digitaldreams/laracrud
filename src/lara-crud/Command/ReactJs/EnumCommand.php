<?php

namespace LaraCrud\Command\ReactJs;

use Illuminate\Console\Command;
use LaraCrud\Crud\ReactJs\ReactJsEnumCrud;
use LaraCrud\Helpers\Helper;
use LaraCrud\Services\ScanDirectoryService;

class EnumCommand extends Command
{
    use Helper;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reactjs:enum {class}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enum generator based on folder or class';

    public function handle()
    {
        $class = $this->argument('class');
        if (class_exists($class)) {
            $enumCrud = new ReactJsEnumCrud($class);
            $enumCrud->save();
        } else {
            $path = $this->toPath($class);
            $fullPath = base_path($path);
            $scan = new ScanDirectoryService($fullPath);
            $files = $scan->scan();
            $s = 0;
            foreach ($files as $file) {
                try {
                    $fullClass = $class . '\\' . pathinfo((string) $file, PATHINFO_FILENAME);

                    if (class_exists($fullClass)) {
                        $enumCrud = new ReactJsEnumCrud($fullClass);
                        $enumCrud->save();
                        $this->info(sprintf('%s file created successfully', $fullClass));
                        ++$s;
                    }
                } catch (\Exception $e) {
                    $this->warn($e->getMessage());
                }
            }
            $this->info(sprintf('%d enum class created out of %d', $s, count($files)));
        }
    }
}
