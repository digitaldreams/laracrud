<?php
/**
 * Created by PhpStorm.
 * User: Tuhin
 * Date: 9/10/2017
 * Time: 5:37 PM
 */

namespace LaraCrud\Console;

use Illuminate\Console\Command;
use LaraCrud\Crud\Transformer as TransformerCrud;


class Transformer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "laracrud:transformer {model} {name?}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a Transformer for API response based on Model';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $model = $this->argument('model');
            $name = $this->argument('name');

            if (class_exists($model)) {
                $modelObj = new $model;
            } else {
                $model = config('laracrud.model.namespace') . '\\' . $model;
                if (!class_exists($model)) {
                    $this->warn($model . ' class does not exists');
                }
                $modelObj = new $model;
            }
            $transformerCrud = new TransformerCrud($modelObj, $name);
            $transformerCrud->save();
            $this->info('Transformer class successfully created');
        } catch (\Exception $ex) {
            $this->error($ex->getMessage() . ' on line ' . $ex->getLine() . ' in ' . $ex->getFile());
        }
    }
}