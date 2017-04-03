<?php
namespace LaraCrud\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;


class Assets extends Command
{
    const TYPE_LAYOUTS = 'layouts';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laracrud:assets {type}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate layouts,templates etc files';

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
        $type = $this->argument('type');

        $this->info('Under developments');

    }
}