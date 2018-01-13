<?php
namespace LaraCrud\Console;

use Illuminate\Console\Command;

class Policy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "laracrud:policy {model} {--controller=} {--name=}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a Policy class based on Controller or Model';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $model = $this->argument('model');
            $controller = $this->option('controller');
            $name = $this->option('name');

            $policyCrud = new \LaraCrud\Crud\Policy($model, $controller, $name);
            $policyCrud->save();
            $this->info('Policy class created successfully');
        } catch (\Exception $ex) {
            $this->error($ex->getMessage() . ' on line ' . $ex->getLine() . ' in ' . $ex->getFile());
        }
    }
}