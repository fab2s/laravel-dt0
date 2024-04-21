# Laravel Dt0

[![CI](https://github.com/fab2s/laravel-dt0/actions/workflows/ci.yml/badge.svg)](https://github.com/fab2s/laravel-dt0/actions/workflows/ci.yml) [![CI](https://github.com/fab2s/laravel-dt0/actions/workflows/ci.yml/badge.svg)](https://github.com/fab2s/laravel-dt0/actions/workflows/ci.yml)

Laravel support for [Dt0](https://github.com/fab2s/dt0), a DTO (_Data-Transport-Object_) PHP implementation than can both secure mutability and implement convenient ways to take control over input and output in various format.

## Installation

`Dt0` can be installed using composer:

```shell
composer require "fab2s/dt0"
```

Once done, you can start playing :

```php

// either get a Dt0 instance or a ValidationException
$dt0 = SomeValidatableDt0::withValidation(...\Illuminate\Http\Request::all());
```

## Requirements

`Dt0` is tested against php 8.1 and 8.2

## Contributing

Contributions are welcome, do not hesitate to open issues and submit pull requests.

## License

`Dt0` is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
