<?php

/*
 * This file is part of fab2s/laravel-dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Laravel\Exceptions;

use fab2s\Dt0\Exception\Dt0Exception;
use Illuminate\Database\Eloquent\Model;

class NotNullableException extends Dt0Exception
{
    public static function make(string $field, Model $model): self
    {
        $modelClass = get_class($model);

        return (new self("Field {$field} is not nullable in model {$modelClass}"))
            ->setContext([
                'model' => $modelClass,
                'data'  => $model->toArray(),
            ])
        ;
    }
}
