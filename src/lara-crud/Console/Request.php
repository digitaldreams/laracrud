<?php

namespace LaraCrud\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class Request extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laracrud:request {table} {name?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a request class based on table';

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
            $table = $this->argument('table');
            $name=  $this->argument('name');

            if ($table == 'all') {
                $modelCrud = new \LaraCrud\RequestCrud();
            } else {
                if (strripos($table, ",")) {
                    $table = explode(",", $table);
                }
                \LaraCrud\LaraCrud::checkMissingTable($table);
                $modelCrud = new \LaraCrud\RequestCrud($table,$name);
            }

            $modelCrud->make();
            $this->info('Request class successfully created');
        } catch (\Exception $ex) {
            $this->error($ex->getMessage());
        }
    }
}