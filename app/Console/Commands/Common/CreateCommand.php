<?php

namespace App\Console\Commands\Common;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

abstract class CreateCommand extends Command
{
    protected string $type;

    protected $basePaths = [
        'controller' => '',
        'model' => '',
        'test' => '',
    ];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->basePaths['controller'] = realpath(__DIR__ . '/../../../Http/Controllers') . '/';
        $this->basePaths['request'] = realpath(__DIR__ . '/../../../Http/Requests') . '/';
        $this->basePaths['model'] = realpath(__DIR__ . '/../../../Models') . '/';
        $this->basePaths['test'] = realpath(__DIR__ . '/../../../../tests/Feature') . '/';
    }

    protected function addStrictTypesKeywordFromFilePath(string $filePath): string
    {
        $controllerFile = collect(file($filePath));
        $willAppendControllerFileContent = $controllerFile->splice(1, $controllerFile->count(), [
            "\n",
            "declare(strict_types=1);\n",
        ]);

        $controllerContent = $controllerFile->merge($willAppendControllerFileContent);

        return $controllerContent->implode('');
    }

    public function create(string $className): int
    {
        $basePath = $this->basePaths[$this->type];
        $nameSpace = collect(explode('\\', $className));
        $willMakeControllerPath = $basePath . $nameSpace->implode('/') . '.php';
        if (! file_exists($willMakeControllerPath)) {
            Artisan::call('make:' . $this->type, [
                'name'  => $className,
            ]);

            file_put_contents($willMakeControllerPath, $this->addStrictTypesKeywordFromFilePath($willMakeControllerPath));
            $this->info($className . ' ' . $this->type . ' created successfully.');
            return 0;
        } else {
            $this->error($className . ' Already Exists !');
            return -1;
        }
    }
}
