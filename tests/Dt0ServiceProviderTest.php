<?php

declare(strict_types=1);

/*
 * This file is part of fab2s/laravel-dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/laravel-dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Laravel\Tests;

use fab2s\Dt0\Attribute\Rule;
use fab2s\Dt0\Laravel\Dt0ServiceProvider;
use fab2s\Dt0\Validator\Validator as Dt0Validator;
use Illuminate\Validation\ValidationException;
use ReflectionProperty;

class Dt0ServiceProviderTest extends TestCase
{
    protected function tearDown(): void
    {
        // do not leave this test's app factory in dt0's static
        Dt0Validator::flush();
        parent::tearDown();
    }

    protected function getPackageProviders($app): array
    {
        return [
            Dt0ServiceProvider::class,
        ];
    }

    public function test_a_rebuilt_app_does_not_validate_with_the_previous_app_factory(): void
    {
        // dt0's built-in validator, built once, as when a Dt0 class is compiled
        $validator = new Dt0Validator;
        $validator->addRule('email', new Rule('required'));
        $this->assertSame(app('validator'), $this->dt0Factory());

        // the app is rebuilt, as between two tests: the provider registers again
        $this->refreshApplication();

        // assertTrue rather than assertNull: a failure would dump the whole factory
        $this->assertTrue($this->dt0Factory() === null, "the provider did not flush dt0's cached factory");

        $this->app->setLocale('fr');
        $this->app['translator']->addLines(['validation.required' => 'Le champ :attribute est obligatoire.'], 'fr');

        try {
            $validator->validate([]);
            $this->fail('validation should have failed');
        } catch (ValidationException $e) {
            $this->assertSame('Le champ email est obligatoire.', $e->errors()['email'][0]);
        }

        $this->assertSame($this->app['validator'], $this->dt0Factory());
    }

    protected function dt0Factory(): mixed
    {
        return (new ReflectionProperty(Dt0Validator::class, 'factory'))->getValue();
    }
}
