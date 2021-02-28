<?php

namespace App\Console\Commands\Create;

use Illuminate\Console\Command;
use App\Console\Commands\Common\CreateCommand;

class Model extends CreateCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'm:m {className}';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create strict Model';


    protected $type = 'model';

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
    public function handle(): int
    {
        $className = $this->argument('className');
        return $this->create($className);
    }
}
