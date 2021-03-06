<?php

namespace App\Console\Commands\Create;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\Common\CreateCommand;

class Controller extends CreateCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'm:c {className}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create strict Controller';

    protected string $type = 'controller';

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
