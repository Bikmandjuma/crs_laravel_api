<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class MakeServiceCommand extends Command
{
    protected $signature = 'make:service {name : The name of the service class}';
    protected $description = 'Create a new service class';
    protected $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    public function handle()
    {
        $name = $this->argument('name');
        $path = app_path("Services/{$name}.php");

        if ($this->files->exists($path)) {
            $this->error("Service {$name} already exists!");
            return;
        }

        $this->makeDirectory($path);

        $stub = $this->getStub();
        $content = str_replace('{{class}}', $name, $stub);

        $this->files->put($path, $content);
        $this->info("Service {$name} created successfully.");
    }

    protected function getStub()
    {
        return <<<EOT
        <?php

        namespace App\Services;

        class {{class}}
        {
            //
        }
        EOT;
    }

    protected function makeDirectory($path)
    {
        $directory = dirname($path);

        if (!$this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory, 0755, true);
        }
    }
}
