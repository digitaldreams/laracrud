<?php
/**
 * Created by PhpStorm.
 * User: Tuhin
 * Date: 9/10/2017
 * Time: 5:37 PM
 */

namespace LaraCrud\Console;

use Illuminate\Console\Command;
use LaraCrud\Crud\Controller as ControllerCrud;


class Package extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "laracrud:package {name} {--only=}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a Laravel Package';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $name = $this->argument('name');
            $only = $this->option('only');
            $onlyArr = !empty($only) ? explode(",", $only) : '';
            $this->info('Package successfully created');
        } catch (\Exception $ex) {
            $this->error($ex->getMessage() . ' on line ' . $ex->getLine() . ' in ' . $ex->getFile());
        }
    }
}