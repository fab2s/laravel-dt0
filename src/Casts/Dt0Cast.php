<?php

/*
 * This file is part of fab2s/laravel-dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Laravel\Casts;

use fab2s\Dt0\Exception\Dt0Exception;
use fab2s\Dt0\Laravel\Dt0;
use fab2s\Dt0\Laravel\Exceptions\NotNullableException;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use JsonException;

class Dt0Cast implements CastsAttributes
{
    /**
     * @var class-string<Dt0>
     */
    protected string $dt0Class;
    protected bool $isNullable = false;

    /**
     * @param class-string<Dt0> $enumClass
     * @param string[]          ...$options
     */
    public function __construct(string $enumClass, ...$options)
    {
        $this->dt0Class = $enumClass;

        $this->isNullable = in_array('nullable', $options);
    }

    /**
     * Cast the given value.
     *
     * @param Model $model
     *
     * @throws NotNullableException
     * @throws JsonException
     * @throws Dt0Exception
     */
    public function get($model, string $key, $value, array $attributes): ?Dt0
    {
        return $this->resolve($model, $key, $value);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param Model $model
     *
     * @throws Dt0Exception
     * @throws NotNullableException
     * @throws JsonException
     */
    public function set($model, string $key, $value, array $attributes): ?string
    {
        return $this->resolve($model, $key, $value)?->toJson();
    }

    /**
     * @throws Dt0Exception
     * @throws NotNullableException
     * @throws JsonException
     */
    protected function resolve(Model $model, string $key, mixed $value): ?Dt0
    {
        if ($value === null) {
            return $this->isNullable ? null : throw NotNullableException::make($key, $model);
        }

        return $this->dt0Class::from($value);
    }
}
