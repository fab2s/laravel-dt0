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
use Illuminate\Container\Container;
use Illuminate\Validation\ValidationException;
use Laravel\Octane\Events\RequestReceived;
use Laravel\Octane\Events\TaskReceived;
use Laravel\Octane\Events\TickReceived;
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

    /**
     * Octane's worker runs each request, task and tick on a clone of the app made
     * current, and flushes the clone afterwards (Laravel\Octane\Worker). This test
     * repeats those steps. Octane is not a dependency: ::class does not load its event
     * classes, so they are dispatched by name, as the provider listens to them.
     */
    public function test_octane_operations_never_validate_with_a_previous_operation_factory(): void
    {
        // dt0's built-in validator, built once, as when a Dt0 class is compiled
        $validator = new Dt0Validator;
        $validator->addRule('email', new Rule('required'));

        // as with Octane's defaults, the base app keeps no validator: each operation builds its own
        $this->app->forgetInstance('validator');

        foreach ([RequestReceived::class, TaskReceived::class, TickReceived::class] as $event) {
            $sandbox = clone $this->app;
            $sandbox->instance('app', $sandbox);
            $sandbox->instance(Container::class, $sandbox);
            Container::setInstance($sandbox);

            $sandbox['events']->dispatch($event);
            $validator->validate(['email' => 'ada@example.com']);

            // assertTrue rather than assertSame: a failure would dump both factories
            $this->assertTrue($this->dt0Factory() === $sandbox['validator'], "after $event, dt0 validated with a previous operation's factory");

            $sandbox->flush();
            Container::setInstance($this->app);
        }
    }

    protected function dt0Factory(): mixed
    {
        return (new ReflectionProperty(Dt0Validator::class, 'factory'))->getValue();
    }
}
