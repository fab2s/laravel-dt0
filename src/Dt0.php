<?php

/*
 * This file is part of fab2s/laravel-dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/laravel-dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Laravel;

use fab2s\Dt0\Dt0 as BaseDt0;
use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Support\Arrayable;

/** @implements Arrayable<string, mixed> */
abstract class Dt0 extends BaseDt0 implements Arrayable, Castable
{
    use LaravelDt0Trait;
}
