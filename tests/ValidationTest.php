<?php

/*
 * This file is part of fab2s/laravel-dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Laravel\Tests;

use fab2s\Dt0\Exception\Dt0Exception;
use fab2s\Dt0\Laravel\Tests\Artifacts\ValidatableDt0;
use Illuminate\Validation\ValidationException;
use JsonException;
use PHPUnit\Framework\Attributes\DataProvider;

class ValidationTest extends \Orchestra\Testbench\TestCase
{
    /**
     * @throws JsonException|Dt0Exception
     */
    #[DataProvider('validationProvider')]
    public function test_enum_dt0(
        array $args,
        array $expected = [],
    ): void {
        try {
            $dt0 = ValidatableDt0::withValidation(...$args);
        } catch (ValidationException $e) {
            $this->assertSame($e->errors(), $expected);

            return;
        }

        $this->assertEquals($args, $dt0->toArray());
    }

    public static function validationProvider(): array
    {
        return [
            [
                'args' => [
                    'string'  => 'UpPer',
                    'int'     => 'abc',
                    'decimal' => 'def',
                ],
                'expected' => [
                    'string' => [
                        'The string must be lowercase.'
                    ],
                    'int' => [
                        'The int field must be an integer.'
                    ],
                    'decimal' => [
                        'The decimal field must have 2 decimal places.'
                    ],

                ],
            ],
            [
                'args' => [
                    'string'  => 'lower',
                    'int'     => '28',
                    'decimal' => '12.00',
                ],
            ],
        ];
    }
}
