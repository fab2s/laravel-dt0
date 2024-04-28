# Laravel Dt0

[![CI](https://github.com/fab2s/laravel-dt0/actions/workflows/ci.yml/badge.svg)](https://github.com/fab2s/laravel-dt0/actions/workflows/ci.yml) [![QA](https://github.com/fab2s/laravel-dt0/actions/workflows/qa.yml/badge.svg)](https://github.com/fab2s/laravel-dt0/actions/workflows/qa.yml) [![codecov](https://codecov.io/gh/fab2s/laravel-dt0/graph/badge.svg?token=YE6AYEDA64)](https://codecov.io/gh/fab2s/laravel-dt0) [![Latest Stable Version](http://poser.pugx.org/fab2s/laravel-dt0/v)](https://packagist.org/packages/fab2s/laravel-dt0) [![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg?style=flat)](http://makeapullrequest.com) [![License](http://poser.pugx.org/fab2s/dt0/license)](https://packagist.org/packages/fab2s/dt0)

[Laravel](https://laravel.com/) support for [fab2s/dt0](https://github.com/fab2s/dt0), a [DTO](https://en.wikipedia.org/wiki/Data_transfer_object) (_Data-Transport-Object_) PHP implementation that can both secure mutability and implement convenient ways to take control over input and output in various formats.

## Installation

`Dt0` can be installed using composer:

```shell
composer require "fab2s/laravel-dt0"
```

## Usage

`Laravel Dt0` only adds Validation implementation and model attribute casting to `Dt0`. All other features will work exactly the same. Have a look at  [`Dt0`](https://github.com/fab2s/dt0) to find out more.

## Caster

In addition to [`Dt0 casters`](https://github.com/fab2s/dt0/tree/main/src/docs/casters.md), `Laravel Dt0` adds a [`CollectionOfCaster`](./src/Caster/CollectionOfCaster.php) which can be used to strongly type a Laravel `Collection`:

````php
    #[Cast(in: new CollectionOfCaster(MyDt0::class))] // Dt0|UnitEnum|ScalarType|string
    public readonly Collection $prop;
````

It can be used as an inspiration to cast into more types.

## Validation

`Laravel Dt0` is able to leverage the full power of Laravel validation on each of its public properties. The validation is performed on the input data prior to any property casting or instantiation.

`Laravel Dt0` comes with a [`Validator`](./src/Validator.php) out of the box that can leverage the full power of [laravel validation](https://laravel.com/docs/master/validation). 

To use it on any `Dt0`, just add the [`Validate`](https://github.com/fab2s/dt0/blob/main/src/Attribute/Validate.php) class attribute :

````php
#[Validate(Validator::class)] // same as #[Validate(new Validator)]
class MyDt0 extends Dt0 {
    // ...
}
````

### Rules can be added in three ways:

- using the second argument of the [`Validate`](https://github.com/fab2s/dt0/blob/main/src/Attribute/Validate.php) **class attribute**:

    ````php
    use fab2s\Dt0\Attribute\Rule;
    use fab2s\Dt0\Attribute\Rules;
    use fab2s\Dt0\Attribute\Validate;
    use fab2s\Dt0\Laravel\Dt0;
    use fab2s\Dt0\Laravel\Validator;
    
    #[Validate(
        Validator::class,
        new Rules(
            propName: new Rule('string|size:2'),
            // ...
        ),
    )]
    class MyDt0 extends Dt0 {
        public readonly string $propName;
    }
    ````

- using the [`Rules`](https://github.com/fab2s/dt0/blob/main/src/Attribute/Rules.php) **class attribute**:

    ````php
    use fab2s\Dt0\Attribute\Rule;
    use fab2s\Dt0\Attribute\Rules;
    use fab2s\Dt0\Attribute\Validate;
    use fab2s\Dt0\Laravel\Dt0;
    use fab2s\Dt0\Laravel\Validator;
    
    #[Validate(Validator::class)]
    #[Rules(
        propName: new Rule(['required', 'string', 'size:2']),
        // ...
    )]
    class MyDt0 extends Dt0 {
        public readonly string $propName;
    }
    ````
  
- using the [`Rule`](https://github.com/fab2s/dt0/blob/main/src/Attribute/Rule.php) **property attribute**:

    ````php
    use fab2s\Dt0\Attribute\Rule;
    use fab2s\Dt0\Attribute\Rules;
    use fab2s\Dt0\Attribute\Validate;
    use fab2s\Dt0\Laravel\Dt0;
    use fab2s\Dt0\Laravel\Validator;
    use fab2s\Dt0\Laravel\Tests\Artifacts\Rules\Lowercase;
    
    #[Validate(Validator::class)]
    class MyDt0 extends Dt0 {
        #[Rule(new Lowercase)] // or any custom rule instance
        public readonly string $propName;
    }
    ````

Combo of the above three are permitted as illustrated in [`ValidatableDt0`](./tests/Artifacts/ValidatableDt0.php). 

> In case of redundancy, priority will be first in `Validate`, `Rules` then `Rule`.
> Dt0 has no opinion of the method used to define rules. They will all perform the same as they are compiled once per process and kept ready for any reuse.

Validation is performed using `withValidation` method:

```php
// either get a Dt0 instance or a ValidationException
$dt0 = SomeValidatableDt0::withValidation(...Request::all());
```

## Model Attribute casting

Should you want to use a `Dt0` as a Laravel Model attribute, you can directly cast it as your `Dt0` thanks to the generic cast [`Dt0Cast`](./src/Casts/Dt0Cast.php). 

Only requirement is for your Dt0 to extend [`fab2s\Dt0\Laravel\Dt0`](./src/Dt0.php) or to extend [`fab2s\Dt0\Dt0`](https://github.com/fab2s/dt0/blob/main/src/Dt0.php) _and_ use [`fab2s\Dt0\Laravel\LaravelDt0Trait`](./src/LaravelDt0Trait.php).

````php
use Illuminate\Database\Eloquent\Model;

class SomeModel extends Model
{
    protected $casts = [
        'some_dt0'          => SomeDt0::class,
        'some_nullable_dt0' => SomeNullableDt0::class.':nullable',
    ];
}

$model = new SomeModel;

$model->some_dt0 = '{"field":"value"}';
// or 
$model->some_dt0 = ['field' => 'value'];
// or 
$model->some_dt0 = SomeDt0::from(['field' => 'value']);

// then
$model->some_dt0->equals(SomeDt0::from('{"field":"value"}')); // true

$model->some_dt0 = null; // throws a NotNullableException
$model->some_nullable_dt0 = null; // works

// can thus be tried
$model->some_nullable_dt0 = SomeNullableDt0::tryFrom($anyInput);
````

## Requirements

`Dt0` is tested against php 8.1 and 8.2 and Laravel 10 / 11

## Contributing

Contributions are welcome, do not hesitate to open issues and submit pull requests.

## License

`Dt0` is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
