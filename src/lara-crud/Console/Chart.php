<?php

namespace LaraCrud\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class Chart extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @option --table one or more table by comma seperated values
     *
     * @option --type type of chart e.g column,bar
     *
     * @option --dashboard  No value need to define just call this option . It will create dashboard
     *
     * @toption --name Name of the page to be saved. Default will be a random name prefix with chart type
     *
     * @var string
     */
    protected $signature = 'laracrud:chart {--table=} {--type=} {--dashboard} {--name=} ';

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
            $table     = $this->option('table');
            $type      = $this->option('type');
            $dashboard = $this->option('dashboard');
            $name      = $this->option('name');

            if (strripos($table, ",")) {
                $table = explode(",", $table);
            }
            \LaraCrud\LaraCrud::checkMissingTable($table);
            $chartCrud = new \LaraCrud\ChartCrud($table, $type, $dashboard, $name);
            $chartCrud->make();

            if (!empty($chartCrud->errors)) {
                $this->error(implode(", ", $chartCrud->errors));
            } else {
                $this->info('chart created successfully');
            }
        } catch (\Exception $ex) {
            $this->error($ex->getMessage().' on line '.$ex->getLine().' in '.$ex->getFile());
        }
    }
}