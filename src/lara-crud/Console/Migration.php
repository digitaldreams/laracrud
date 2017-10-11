<?php

namespace LaraCrud\Console;

use Illuminate\Console\Command;
use LaraCrud\Crud\MigrationCrud;

class Migration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "laracrud:migration {table}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a migration class based on table';

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
            if (strripos($table, ",")) {
                $tables = explode(",", $table);
                foreach ($tables as $table) {
                    $migrationCrud = new MigrationCrud($table);
                    $migrationCrud->save();
                }
                $this->info('Migration classes successfully created');
            } else {
                MigrationCrud::checkMissingTable($table);
                $migrationCrud = new MigrationCrud($table);
                $migrationCrud->save();

                if (!empty($migrationCrud->errors)) {
                    $this->error(implode(", ", $migrationCrud->errors));
                } else {
                    $this->info('Migration class successfully created');
                }
            }

        } catch (\Exception $ex) {
            $this->error($ex->getMessage() . ' on line ' . $ex->getLine() . ' in ' . $ex->getFile());
        }
    }
}