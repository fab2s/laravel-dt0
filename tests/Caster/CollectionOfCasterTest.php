<?php

/*
 * This file is part of fab2s/laravel-dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/laravel-dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Laravel\Tests\Caster;

use Exception;
use fab2s\Dt0\Caster\ScalarType;
use fab2s\Dt0\Exception\CasterException;
use fab2s\Dt0\Exception\Dt0Exception;
use fab2s\Dt0\Laravel\Caster\CollectionOfCaster;
use fab2s\Dt0\Laravel\Tests\Artifacts\DumbDt0;
use fab2s\Dt0\Laravel\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class CollectionOfCasterTest extends TestCase
{
    /**
     * @throws Exception
     */
    #[DataProvider('castProvider')]
    public function test_cast(ScalarType|string $type, $value, $expected): void
    {
        $caster = new CollectionOfCaster($type);
        $casted = $caster->cast($value);

        $this->assertSame(json_encode($expected), json_encode($caster->cast($value)));
    }

    public function test_exception(): void
    {
        $this->expectException(CasterException::class);
        new CollectionOfCaster('NotAType');
    }

    public function test_scalar_exception(): void
    {
        $this->expectException(Dt0Exception::class);
        $caster = new CollectionOfCaster(ScalarType::bool);
        $caster->cast([[]]);
    }

    public static function castProvider(): array
    {
        return [
            [
                'type'  => DumbDt0::class,
                'value' => [
                    DumbDt0::make(prop1: 'ONE', prop2: 'ONE', prop3: 'ONE'),
                    ['prop1' => 'TWO', 'prop2' => 'TWO', 'prop3' => 'TWO'],
                    '{"prop1":"three","prop2":"three","prop3":"three"}',
                ],
                'expected' => collect([
                    DumbDt0::make(prop1: 'ONE', prop2: 'ONE', prop3: 'ONE'),
                    DumbDt0::make(prop1: 'TWO', prop2: 'TWO', prop3: 'TWO'),
                    DumbDt0::make(prop1: 'three', prop2: 'three', prop3: 'three'),
                ]),
            ],
            [
                'type'  => 'string',
                'value' => collect([
                    'ONE',
                    'TWO',
                    'three',
                ]),
                'expected' => collect([
                    'ONE',
                    'TWO',
                    'three',
                ]),
            ],
            [
                'type'  => 'int',
                'value' => [
                    null,
                    '42',
                    42.42,
                    '1337.1337',
                ],
                'expected' => collect([
                    0,
                    42,
                    42,
                    1337,
                ]),
            ],
            [
                'type'     => ScalarType::bool,
                'value'    => null,
                'expected' => null,
            ],
        ];
    }
}
