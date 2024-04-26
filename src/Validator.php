<?php

/*
 * This file is part of fab2s/laravel-dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Laravel;

use fab2s\Dt0\Attribute\Rule;
use fab2s\Dt0\Validator\ValidatorInterface;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\ValidationException;

class Validator implements ValidatorInterface
{
    public array $rules = [];

    /**
     * @throws ValidationException
     */
    public function validate(array $data): array
    {
        return ValidatorFacade::make($data, $this->rules)->validate();
    }

    public function addRule(string $name, Rule $rule): static
    {
        $this->rules[$name] = $rule->rule;

        return $this;
    }
}
