<?php

declare(strict_types=1);

/*
 * This file is part of fab2s/laravel-dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/laravel-dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Laravel;

use fab2s\Dt0\Laravel\Console\MakeDt0Command;
use fab2s\Dt0\Validator\Validator as Dt0Validator;
use Illuminate\Support\ServiceProvider;

class Dt0ServiceProvider extends ServiceProvider
{
    /**
     * No return type, like Laravel's ServiceProvider::register(), so that
     * subclasses declaring an untyped register() stay compatible.
     *
     * @return void
     */
    public function register()
    {
        // dt0's built-in Validator caches the validation factory of the app that
        // resolved it. This runs once per app (once per test, once per request
        // under PHP-FPM), so a rebuilt app never validates with a previous one's factory.
        Dt0Validator::flush();
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([MakeDt0Command::class]);
        }
    }
}
