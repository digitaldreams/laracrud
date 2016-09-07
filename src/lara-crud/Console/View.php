<?php

namespace LaraCrud\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class View extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laracrud:view {table} {page?} {type?}';

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
            $type  = $this->argument('type');
            $page  = $this->argument('page');

            if (strripos($table, ",")) {
                $table = explode(",", $table);
            }
            \LaraCrud\LaraCrud::checkMissingTable($table);
            $modelCrud = new \LaraCrud\ViewCrud($table, $page, $type);
            $modelCrud->make();
            $this->info('View created successfully');
        } catch (\Exception $ex) {
            $this->error($ex->getMessage().' on '.$ex->getLine().' in '.$ex->getFile());
        }
    }
}