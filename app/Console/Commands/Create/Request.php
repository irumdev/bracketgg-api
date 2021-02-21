<?php

namespace App\Console\Commands\Create;

use Illuminate\Console\Command;
use App\Console\Commands\Common\CreateCommand;

class Request extends CreateCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'm:r {className}';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create strict Request';


    protected $type = 'request';

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
     * @return int
     */
    public function handle()
    {
        $className = $this->argument('className');
        $this->create($className);
    }
}
