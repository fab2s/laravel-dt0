<?php

/*
 * This file is part of fab2s/laravel-dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/laravel-dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Laravel\Tests\Artifacts;

use Illuminate\Database\Eloquent\Model;

class EncryptedCastModel extends Model
{
    protected $table   = 'table';
    protected $guarded = [];
    protected $casts   = [
        'encrypted_dt0' => EncryptedDt0::class,
    ];
}
