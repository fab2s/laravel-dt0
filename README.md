# Laravel Dt0

[![CI](https://github.com/fab2s/laravel-dt0/actions/workflows/ci.yml/badge.svg)](https://github.com/fab2s/laravel-dt0/actions/workflows/ci.yml) [![QA](https://github.com/fab2s/laravel-dt0/actions/workflows/qa.yml/badge.svg)](https://github.com/fab2s/laravel-dt0/actions/workflows/qa.yml) [![codecov](https://codecov.io/gh/fab2s/laravel-dt0/graph/badge.svg?token=YE6AYEDA64)](https://codecov.io/gh/fab2s/laravel-dt0) [![Latest Stable Version](http://poser.pugx.org/fab2s/laravel-dt0/v)](https://packagist.org/packages/fab2s/laravel-dt0) [![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg?style=flat)](http://makeapullrequest.com) [![License](http://poser.pugx.org/fab2s/dt0/license)](https://packagist.org/packages/fab2s/dt0)

Laravel support for [fab2s/dt0](https://github.com/fab2s/dt0), a DTO (_Data-Transport-Object_) PHP implementation than can both secure mutability and implement convenient ways to take control over input and output in various formats.

## Installation

`Dt0` can be installed using composer:

```shell
composer require "fab2s/laravel-dt0"
```

## Validation

Laravel `Dt0` is able to leverage the full power of Laravel validation on each of its properties. The validation is performed on the input data prior to any property casting or instantiation.

```php

// either get a Dt0 instance or a ValidationException
$dt0 = SomeValidatableDt0::withValidation(...\Illuminate\Http\Request::all());
```

## Model Attribute casting

Should you want to use a `Dt0` as a Laravel Model attribute, you cas use [Dt0Cast](./src/Casts/Dt0Cast.php) to cast it.

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
