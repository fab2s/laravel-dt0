<?php

/*
 * This file is part of fab2s/laravel-dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/laravel-dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Laravel\Tests\Artifacts;

use fab2s\Dt0\Attribute\Cast;
use fab2s\Dt0\Laravel\Caster\EncryptedCaster;
use fab2s\Dt0\Laravel\Dt0;

class EncryptedDt0 extends Dt0
{
    #[Cast(in: new EncryptedCaster, out: new EncryptedCaster)]
    public readonly ?string $secret;
}
