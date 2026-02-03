<?php

/*
 * This file is part of fab2s/laravel-dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/laravel-dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Laravel\Tests;

use fab2s\Dt0\Laravel\Tests\Artifacts\ClassRulesPriorityDt0;
use fab2s\Dt0\Laravel\Tests\Artifacts\PropertyRulePriorityDt0;
use fab2s\Dt0\Laravel\Tests\Artifacts\ValidateRulesPriorityDt0;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;

class ValidationPriorityTest extends TestCase
{
    public function test_property_rule_has_highest_priority(): void
    {
        // 6 chars: passes Property Rule (min:5), would fail class Rules (min:50) and Validate Rules (min:100)
        $dt0 = PropertyRulePriorityDt0::withValidation(priority: 'sixchr');

        $this->assertSame('sixchr', $dt0->priority);
    }

    public function test_property_rule_validation_fails_correctly(): void
    {
        $this->expectException(ValidationException::class);

        // 4 chars: fails even Property Rule (min:5)
        PropertyRulePriorityDt0::withValidation(priority: 'four');
    }

    public function test_class_rules_has_priority_over_validate_rules(): void
    {
        // 15 chars: passes class Rules (min:10), would fail Validate Rules (min:100)
        $dt0 = ClassRulesPriorityDt0::withValidation(priority: 'fifteencharsss');

        $this->assertSame('fifteencharsss', $dt0->priority);
    }

    public function test_class_rules_validation_fails_correctly(): void
    {
        $this->expectException(ValidationException::class);

        // 8 chars: fails class Rules (min:10)
        ClassRulesPriorityDt0::withValidation(priority: 'eightchr');
    }

    /**
     * Test that #[Validate] Rules are used when no other rules exist
     */
    public function test_validate_rules_used_as_fallback(): void
    {
        // 25 chars: passes Validate Rules (min:20)
        $dt0 = ValidateRulesPriorityDt0::withValidation(priority: 'twentyfivecharactershere');

        $this->assertSame('twentyfivecharactershere', $dt0->priority);
    }

    public function test_validate_rules_validation_fails_correctly(): void
    {
        $this->expectException(ValidationException::class);

        // 15 chars: fails Validate Rules (min:20)
        ValidateRulesPriorityDt0::withValidation(priority: 'fifteencharsss');
    }

    #[DataProvider('priorityProvider')]
    public function test_validation_priority(string $dtoClass, string $value, bool $shouldPass): void
    {
        if ($shouldPass) {
            $dt0 = $dtoClass::withValidation(priority: $value);
            $this->assertSame($value, $dt0->priority);
        } else {
            $this->expectException(ValidationException::class);
            $dtoClass::withValidation(priority: $value);
        }
    }

    public static function priorityProvider(): array
    {
        return [
            // Property Rule (min:5)
            'property rule: 6 chars passes' => [PropertyRulePriorityDt0::class, 'sixchr', true],
            'property rule: 4 chars fails'  => [PropertyRulePriorityDt0::class, 'four', false],

            // Class Rules (min:10)
            'class rules: 15 chars passes' => [ClassRulesPriorityDt0::class, 'fifteencharsss', true],
            'class rules: 8 chars fails'   => [ClassRulesPriorityDt0::class, 'eightchr', false],

            // Validate Rules (min:20)
            'validate rules: 25 chars passes' => [ValidateRulesPriorityDt0::class, 'twentyfivecharactershere', true],
            'validate rules: 15 chars fails'  => [ValidateRulesPriorityDt0::class, 'fifteencharsss', false],
        ];
    }
}
