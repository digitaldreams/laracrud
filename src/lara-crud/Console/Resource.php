<?php


namespace LaraCrud\Console;

use Illuminate\Console\Command;
use LaraCrud\Helpers\Helper;


class Resource extends Command
{
    use Helper;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laracrud:resource
        {model : Eloquent Model name}
        {name? : Custom name of your Resource. e.g. PostResource }
        {--c|createAll : create related Resource classes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a API resource class based on Model';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {

        } catch (\Exception $e) {

        }

    }
}
