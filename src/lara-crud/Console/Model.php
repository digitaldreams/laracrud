<?php

namespace LaraCrud\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class Model extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "laracrud:model {table} {name?}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a Model class based on table';

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
            $table     = $this->argument('table');
            $modelName = $this->argument('name');
            if ($table == 'all') {
                $modelCrud = new \LaraCrud\ModelCrud();
            } else {
                if (strripos($table, ",")) {
                    $table = explode(",", $table);
                }
                \LaraCrud\LaraCrud::checkMissingTable($table);
                $modelCrud = new \LaraCrud\ModelCrud($table, $modelName);
            }

            $modelCrud->make();

            if (!empty($modelCrud->errors)) {
                $this->error(implode(", ", $modelCrud->errors));
            } else {
                $this->info('Model class successfully created');
            }
        } catch (\Exception $ex) {
            $this->error($ex->getMessage().' on line '.$ex->getLine().' in '.$ex->getFile());
        }
    }
}