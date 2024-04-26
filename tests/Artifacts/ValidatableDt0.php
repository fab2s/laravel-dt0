<?php

/*
 * This file is part of fab2s/laravel-dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Laravel\Tests\Artifacts;

use fab2s\Dt0\Attribute\Rule;
use fab2s\Dt0\Attribute\Rules;
use fab2s\Dt0\Attribute\Validate;
use fab2s\Dt0\Laravel\Dt0;
use fab2s\Dt0\Laravel\Tests\Artifacts\Rules\Lowercase;
use fab2s\Dt0\Laravel\Validator;

#[Validate(
    Validator::class,
    new Rules(
        string: new Rule(new Lowercase),
    ),
)]
#[Rules(
    int: new Rule('integer|min:0'),
)]
class ValidatableDt0 extends Dt0
{
    public readonly string $string;
    public readonly int $int;

    #[Rule(['decimal:2'])]
    public readonly string $decimal;
}
