<?php

namespace LaraCrud\Console\ReactJs;

use Illuminate\Console\Command;

class FormCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reactjs:form {model} {controller}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enum Generator based on Folder or Class';

    public function handle()
    {

    }
}
