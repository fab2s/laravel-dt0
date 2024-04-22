<?php

/*
 * This file is part of fab2s/laravel-dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Laravel\Tests\Casts;

use fab2s\Dt0\Exception\Dt0Exception;
use fab2s\Dt0\Laravel\Casts\Dt0Cast;
use fab2s\Dt0\Laravel\Dt0;
use fab2s\Dt0\Laravel\Exceptions\NotNullableException;
use fab2s\Dt0\Laravel\Tests\Artifacts\CastModel;
use fab2s\Dt0\Laravel\Tests\Artifacts\DumbDt0;
use fab2s\Dt0\Laravel\Tests\TestCase;
use JsonException;
use PHPUnit\Framework\Attributes\DataProvider;

class Dt0castTest extends TestCase
{
    /**
     * @param DumbDt0|class-string<NotNullableException>|null $expected
     *
     * @throws Dt0Exception
     * @throws NotNullableException
     * @throws JsonException
     */
    #[DataProvider('castProvider')]
    public function test_dt0_cast_get(
        Dt0|string|array|null $value,
        DumbDt0|string|null $expected,
        array $options = [],
    ): void {
        $cast = new Dt0cast(DumbDt0::class, ...$options);

        switch (true) {
            case is_object($expected):
                $this->assertTrue($expected->equal($cast->get(new CastModel, 'key', $value, [])));
                break;
            case is_string($expected):
                $this->expectException(NotNullableException::class);
                $cast->get(new CastModel, 'key', $value, []);
                break;
            case $expected === null:
                $this->assertNull($cast->get(new CastModel, 'key', $value, []));
                break;
        }
    }

    /**
     * @param DumbDt0|class-string<NotNullableException>|null $expected
     *
     * @throws Dt0Exception
     * @throws NotNullableException
     * @throws JsonException
     */
    #[DataProvider('castProvider')]
    public function test_dt0_cast_set(
        Dt0|string|array|null $value,
        DumbDt0|string|null $expected,
        array $options = [],
    ): void {
        $cast = new Dt0cast(DumbDt0::class, ...$options);

        switch (true) {
            case is_object($expected):
                $this->assertSame($expected->toJson(), $cast->set(new CastModel, 'key', $value, []));
                break;
            case is_string($expected):
                $this->expectException(NotNullableException::class);
                $cast->set(new CastModel, 'key', $value, []);
                break;
            case $expected === null:
                $this->assertSame(null, $cast->set(new CastModel, 'key', $value, []));
                break;
        }
    }

    /**
     * @throws Dt0Exception
     * @throws JsonException
     */
    public static function castProvider(): array
    {
        $input = [
            'prop1' => 'prop1',
            'prop2' => 'prop2',
            'prop3' => 'prop3',
        ];

        $instance = DumbDt0::fromArray($input);

        return [
            [
                'value'    => null,
                'expected' => null,
                'options'  => ['nullable'],
            ],
            [
                'value'    => $input,
                'expected' => $instance,
                'options'  => ['nullable'],
            ],
            [
                'value'    => $input,
                'expected' => $instance,
            ],
            [
                'value'    => null,
                'expected' => NotNullableException::class,
            ],
            [
                'value'    => json_encode($input),
                'expected' => $instance,
                'options'  => ['nullable'],
            ],
            [
                'value'    => $instance,
                'expected' => $instance,
                'options'  => ['nullable'],
            ],
        ];
    }
}
