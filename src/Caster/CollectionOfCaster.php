<?php

/*
 * This file is part of fab2s/laravel-dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Laravel\Caster;

use fab2s\Dt0\Caster\ArrayType;
use fab2s\Dt0\Caster\CasterInterface;
use fab2s\Dt0\Caster\ScalarCaster;
use fab2s\Dt0\Caster\ScalarType;
use fab2s\Dt0\Dt0;
use fab2s\Dt0\Exception\CasterException;
use fab2s\Dt0\Property\Property;
use Illuminate\Support\Collection;

class CollectionOfCaster implements CasterInterface
{
    public readonly ArrayType|ScalarType|string $logicalType;
    protected ?ScalarCaster $scalarCaster;

    /**
     * @throws CasterException
     */
    public function __construct(
        /** @var class-string<Dt0|UnitEnum>|ScalarType|string */
        public readonly ScalarType|string $type,
    ) {
        if (is_string($type)) {
            $logicalType = match (true) {
                is_subclass_of($type, Dt0::class)      => ArrayType::DT0,
                is_subclass_of($type, UnitEnum::class) => ArrayType::ENUM,
                default                                => ScalarType::tryFrom($type),
            };
        } else {
            $logicalType = $type;
        }

        if (! $logicalType) {
            throw new CasterException('[' . Dt0::classBasename(static::class) . "] $type is not an ArrayType nor a ScalarType");
        }

        $this->logicalType  = $logicalType;
        $this->scalarCaster = $this->logicalType instanceof ScalarType ? new ScalarCaster($this->logicalType) : null;
    }

    public function cast(mixed $value): ?Collection
    {
        if (! is_iterable($value)) {
            return null;
        }

        $result = Collection::make();

        foreach ($value as $item) {
            $result->push(match ($this->logicalType) {
                ArrayType::DT0  => $this->type::tryFrom($item),
                ArrayType::ENUM => Property::tryEnum($this->type, $item),
                default         => $this->scalarCaster->cast($item),
            });
        }

        return $result;
    }
}
