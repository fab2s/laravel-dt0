<?php

declare(strict_types=1);

/*
 * This file is part of fab2s/laravel-dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/laravel-dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Laravel\Console;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'make:dt0')]
class MakeDt0Command extends GeneratorCommand
{
    protected $signature = 'make:dt0 
                              { name : The name of the Dt0 class } 
                              { --validated : Create with validation scaffolding }';
    protected $description = 'Create a new Dt0 class';
    protected $type        = 'Dt0';

    protected function getStub(): string
    {
        return $this->option('validated')
            ? $this->resolveStubPath('/stubs/dt0.validated.stub')
            : $this->resolveStubPath('/stubs/dt0.stub');
    }

    protected function resolveStubPath(string $stub): string
    {
        $customPath = $this->laravel->basePath(trim($stub, '/'));

        return file_exists($customPath)
            ? $customPath
            : dirname(__DIR__, 2) . $stub;
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\Dto';
    }
}
