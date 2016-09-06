<?php

namespace LaraCrud\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class Controller extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tb:controller {model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a Controller class based on Model';

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
            $table = $this->argument('model');
            $modelCrud = new \App\Libs\ControllerCrud($table);
            $modelCrud->make();
            $this->info('Controller class created successfully');
        } catch (\Exception $ex) {
            $this->error($ex->getMessage());
        }
    }

}
