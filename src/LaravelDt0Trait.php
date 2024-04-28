<?php

/*
 * This file is part of fab2s/laravel-dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/laravel-dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Laravel;

use fab2s\Dt0\Laravel\Casts\Dt0Cast;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

trait LaravelDt0Trait
{
    public static function castUsing(array $arguments): CastsAttributes
    {
        return new Dt0Cast(static::class, ...$arguments);
    }
}
