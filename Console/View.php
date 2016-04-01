<?php

namespace App\Libs\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class View extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tb:view {table} {type=form} {style=table}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create view based on table';

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
            $type = $this->argument('type');
            $style = $this->argument('style');

            $modelCrud = new \App\Libs\ViewCrud($table);
            print_r($modelCrud->make());
            $this->info('View created successfully');
        } catch (\Exception $ex) {
            $this->error($ex->getMessage());
        }
    }

}
