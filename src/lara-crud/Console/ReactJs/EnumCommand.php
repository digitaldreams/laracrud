<?php

namespace LaraCrud\Console\ReactJs;

use Illuminate\Console\Command;

class EnumCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reactjs:enum {class}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enum generator based on folder or class';

    public function handle()
    {

    }
}
