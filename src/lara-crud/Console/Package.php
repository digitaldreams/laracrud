<?php
/**
 * Created by PhpStorm.
 * User: Tuhin
 * Date: 9/10/2017
 * Time: 5:37 PM
 */

namespace LaraCrud\Console;

use Illuminate\Console\Command;
use LaraCrud\Crud\Package as PackageCrud;

class Package extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "laracrud:package {name : Name of the Package e.g. Post}";

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
            $package = new PackageCrud($name);
            $package->save();

            $this->info('Package successfully created');
        } catch (\Exception $ex) {
            $this->error($ex->getMessage() . ' on line ' . $ex->getLine() . ' in ' . $ex->getFile());
        }
    }
}
