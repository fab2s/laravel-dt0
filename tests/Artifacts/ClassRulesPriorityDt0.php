<?php

/*
 * This file is part of fab2s/laravel-dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/laravel-dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Laravel\Tests\Artifacts;

use fab2s\Dt0\Attribute\Rule;
use fab2s\Dt0\Attribute\Rules;
use fab2s\Dt0\Attribute\Validate;
use fab2s\Dt0\Laravel\Dt0;
use fab2s\Dt0\Laravel\Validator;

/**
 * Test DTO: Class Rules should win over Validate Rules
 */
#[Validate(
    Validator::class,
    new Rules(
        priority: new Rule('min:100'),  // Lowest priority - requires min 100 chars
    ),
)]
#[Rules(
    priority: new Rule('min:10'),  // Wins - requires min 10 chars
)]
class ClassRulesPriorityDt0 extends Dt0
{
    public readonly string $priority;
}
