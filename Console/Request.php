<?php

namespace App\Libs\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class Request extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tb:request {table}';

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
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        try {
            $table = $this->argument('table');

            if ($table == 'all') {
                $modelCrud = new \App\Libs\RequestCrud();
            } else {
                if (strripos($table, ",")) {
                    $table = explode(",", $table);
                }
                $modelCrud = new \App\Libs\RequestCrud($table);
            }

            $modelCrud->make();
            $this->info('Request class successfully created');
        } catch (\Exception $ex) {
            $this->error($ex->getMessage());
        }
    }

}
