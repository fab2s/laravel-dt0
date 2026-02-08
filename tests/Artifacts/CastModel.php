<?php

/*
 * This file is part of fab2s/laravel-dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/laravel-dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Laravel\Tests\Artifacts;

use Illuminate\Database\Eloquent\Model;

/**
 * @property DumbDt0|array<string, mixed>|string|null $some_dt0
 * @property DumbDt0|array<string, mixed>|string|null $some_nullable_dt0
 */
class CastModel extends Model
{
    protected $table   = 'table';
    protected $guarded = [];
    protected $casts   = [
        'some_dt0'          => DumbDt0::class,
        'some_nullable_dt0' => DumbDt0::class . ':nullable',
    ];
}
