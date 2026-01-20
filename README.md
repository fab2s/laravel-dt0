# Laravel Dt0

[![CI](https://github.com/fab2s/laravel-dt0/actions/workflows/ci.yml/badge.svg)](https://github.com/fab2s/laravel-dt0/actions/workflows/ci.yml) [![QA](https://github.com/fab2s/laravel-dt0/actions/workflows/qa.yml/badge.svg)](https://github.com/fab2s/laravel-dt0/actions/workflows/qa.yml) [![codecov](https://codecov.io/gh/fab2s/laravel-dt0/graph/badge.svg?token=YE6AYEDA64)](https://codecov.io/gh/fab2s/laravel-dt0) [![Latest Stable Version](http://poser.pugx.org/fab2s/laravel-dt0/v)](https://packagist.org/packages/fab2s/laravel-dt0) [![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg?style=flat)](http://makeapullrequest.com) [![License](http://poser.pugx.org/fab2s/dt0/license)](https://packagist.org/packages/fab2s/dt0)

A powerful [Laravel](https://laravel.com/) integration for [fab2s/dt0](https://github.com/fab2s/dt0), bringing **true immutability**, **Laravel validation**, and **Eloquent model casting** to your Data Transfer Objects.

## Why Dt0?

Traditional DTOs with mutable properties miss the core purpose: **guaranteeing that data won't be accidentally modified**. Dt0 leverages PHP 8.1+'s native `readonly` properties to enforce immutability at the language level, not by convention, but by design.

**Key Benefits:**
- **True Immutability** — Readonly properties prevent accidental modifications (fatal error, not silent bug)
- **Laravel Validation** — Full power of Laravel's validation rules on DTO properties
- **Eloquent Casting** — Use DTOs directly as model attributes with automatic serialization
- **Flexible Hydration** — Create from arrays, JSON strings, or other instances
- **Type Safety** — Strong typing with bidirectional casting support
- **Performance** — Logic compiled once per process and cached

> **Note:** This package extends [fab2s/dt0](https://github.com/fab2s/dt0) with Laravel-specific features (validation, Eloquent casting). All features from the base package, including [property casting](https://github.com/fab2s/dt0/blob/0.0.2/docs/casters.md), property renaming, default values, output filtering, and more, work seamlessly here. Visit the [dt0 documentation](https://github.com/fab2s/dt0) for the complete feature set.

## Table of Contents

- [Installation](#installation)
- [Quick Start](#quick-start)
- [Core Features](#core-features)
  - [Creating DTOs](#creating-dtos)
  - [Factory Methods](#factory-methods)
  - [Serialization](#serialization)
  - [Immutable Updates](#immutable-updates)
- [Laravel Validation](#laravel-validation)
  - [Defining Rules](#defining-rules)
  - [Custom Validation Rules](#custom-validation-rules)
- [Model Attribute Casting](#model-attribute-casting)
- [Casters](#casters)
  - [Built-in Casters](#built-in-casters)
  - [CollectionOfCaster](#collectionofcaster)
- [Compatibility](#compatibility)
- [Contributing](#contributing)
- [License](#license)

## Installation

```shell
composer require fab2s/laravel-dt0
```

## Quick Start

```php
use fab2s\Dt0\Attribute\Rule;
use fab2s\Dt0\Attribute\Validate;
use fab2s\Dt0\Laravel\Dt0;
use fab2s\Dt0\Laravel\Validator;

#[Validate(Validator::class)]
class UserDto extends Dt0
{
    #[Rule(['required', 'string', 'max:255'])]
    public readonly string $name;

    #[Rule(['required', 'email'])]
    public readonly string $email;

    #[Rule(['nullable', 'integer', 'min:0'])]
    public readonly ?int $age;
}

// Create with validation (throws ValidationException on failure)
$user = UserDto::withValidation(
    name: 'John Doe',
    email: 'john@example.com',
    age: 30,
);

// Or create from various sources
$user = UserDto::from(['name' => 'John', 'email' => 'john@example.com']);
$user = UserDto::fromJson('{"name":"John","email":"john@example.com"}');

// Immutable — this triggers a fatal error:
// $user->name = 'Jane'; // Error!

// Serialize
$user->toArray();  // ['name' => 'John', 'email' => 'john@example.com', 'age' => 30]
$user->toJson();   // {"name":"John","email":"john@example.com","age":30}
```

## Core Features

### Creating DTOs

Extend `fab2s\Dt0\Laravel\Dt0` for full Laravel integration:

```php
use fab2s\Dt0\Laravel\Dt0;

class ProductDto extends Dt0
{
    public readonly string $name;
    public readonly float $price;
    public readonly ?string $description;
}
```

### Factory Methods

Dt0 provides multiple ways to instantiate:

```php
// Named arguments
$dto = new ProductDto(name: 'Widget', price: 19.99, description: null);

// Static factory
$dto = ProductDto::make(name: 'Widget', price: 19.99);

// From array
$dto = ProductDto::fromArray(['name' => 'Widget', 'price' => 19.99]);

// From JSON
$dto = ProductDto::fromJson('{"name":"Widget","price":19.99}');

// Polymorphic (accepts array, JSON string, or instance)
$dto = ProductDto::from($mixedInput);

// Safe version (returns null instead of throwing)
$dto = ProductDto::tryFrom($mixedInput);
```

### Serialization

```php
$dto->toArray();      // Array with objects preserved
$dto->toJsonArray();  // Array with jsonSerialize() called on nested objects
$dto->toJson();       // JSON string
(string) $dto;        // Also returns JSON (Stringable)
```

### Immutable Updates

Create modified copies without mutating the original:

```php
$original = ProductDto::from(['name' => 'Widget', 'price' => 19.99]);

// Clone with modifications
$updated = $original->update(price: 24.99);

// Compare instances
$original->equals($updated); // false
```

## Laravel Validation

Laravel Dt0 integrates seamlessly with [Laravel's validation system](https://laravel.com/docs/master/validation). Validation runs on input data **before** any casting or instantiation.

### Defining Rules

Rules can be defined in three ways (combinable):

#### 1. Via `Validate` Class Attribute

```php
use fab2s\Dt0\Attribute\Rule;
use fab2s\Dt0\Attribute\Rules;
use fab2s\Dt0\Attribute\Validate;
use fab2s\Dt0\Laravel\Dt0;
use fab2s\Dt0\Laravel\Validator;

#[Validate(
    Validator::class,
    new Rules(
        name: new Rule('required|string|max:255'),
        email: new Rule('required|email'),
    ),
)]
class UserDto extends Dt0
{
    public readonly string $name;
    public readonly string $email;
}
```

#### 2. Via `Rules` Class Attribute

```php
#[Validate(Validator::class)]
#[Rules(
    name: new Rule(['required', 'string', 'max:255']),
    email: new Rule(['required', 'email']),
)]
class UserDto extends Dt0
{
    public readonly string $name;
    public readonly string $email;
}
```

#### 3. Via `Rule` Property Attribute

```php
#[Validate(Validator::class)]
class UserDto extends Dt0
{
    #[Rule(['required', 'string', 'max:255'])]
    public readonly string $name;

    #[Rule(['required', 'email'])]
    public readonly string $email;
}
```

> **Priority:** When rules are defined in multiple places, the priority is: `Validate` > `Rules` > `Rule`

### Triggering Validation

```php
use Illuminate\Validation\ValidationException;

try {
    $dto = UserDto::withValidation(...$request->all());
} catch (ValidationException $e) {
    // Handle validation errors
    $errors = $e->errors();
}
```

### Custom Validation Rules

Use Laravel's custom rule classes:

```php
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Lowercase implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (strtolower($value) !== $value) {
            $fail('The :attribute must be lowercase.');
        }
    }
}

#[Validate(Validator::class)]
class SlugDto extends Dt0
{
    #[Rule(new Lowercase)]
    public readonly string $slug;
}
```

## Model Attribute Casting

Use DTOs directly as Eloquent model attributes with automatic JSON serialization:

```php
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $casts = [
        'shipping_address' => AddressDto::class,
        'billing_address'  => AddressDto::class . ':nullable',
    ];
}
```

### Usage

```php
$order = new Order;

// Set from array
$order->shipping_address = ['street' => '123 Main St', 'city' => 'NYC'];

// Set from JSON
$order->shipping_address = '{"street":"123 Main St","city":"NYC"}';

// Set from DTO instance
$order->shipping_address = AddressDto::from(['street' => '123 Main St', 'city' => 'NYC']);

// Access as DTO
echo $order->shipping_address->city; // 'NYC'

// Compare
$order->shipping_address->equals(AddressDto::from(['street' => '123 Main St', 'city' => 'NYC'])); // true

// Nullable handling
$order->billing_address = null; // OK (has :nullable modifier)
$order->shipping_address = null; // Throws NotNullableException
```

### Requirements

Your DTO must either:
- Extend `fab2s\Dt0\Laravel\Dt0`, or
- Extend `fab2s\Dt0\Dt0` and use `fab2s\Dt0\Laravel\LaravelDt0Trait`

## Casters

Casters transform property values during hydration (input) and serialization (output).

### Built-in Casters

From [`fab2s/dt0`](https://github.com/fab2s/dt0/blob/0.0.2/docs/casters.md):

| Caster | Description |
|--------|-------------|
| `ScalarCaster` | Converts to `int`, `float`, `bool`, or `string` |
| `ArrayOfCaster` | Casts each array element to a type (scalar, Dt0, or enum) |
| `DateTimeCaster` | Parses to `DateTime`/`DateTimeImmutable` |
| `CarbonCaster` | Parses to `Carbon`/`CarbonImmutable` (requires `nesbot/carbon`) |
| `DateTimeFormatCaster` | Formats DateTime to string |
| `MathCaster` | High-precision decimals (requires `fab2s/math`) |
| `Dt0Caster` | Explicit casting to a Dt0 class |
| `ClassCaster` | Instantiates arbitrary classes |

### CollectionOfCaster

Laravel Dt0 adds `CollectionOfCaster` for strongly-typed Laravel Collections:

```php
use fab2s\Dt0\Attribute\Cast;
use fab2s\Dt0\Laravel\Caster\CollectionOfCaster;
use fab2s\Dt0\Laravel\Dt0;
use Illuminate\Support\Collection;

class OrderDto extends Dt0
{
    public readonly string $orderId;

    #[Cast(in: new CollectionOfCaster(OrderItemDto::class))]
    public readonly Collection $items;
}

// Each item in the array is cast to OrderItemDto
$order = OrderDto::from([
    'orderId' => 'ORD-123',
    'items' => [
        ['sku' => 'ABC', 'quantity' => 2],
        ['sku' => 'XYZ', 'quantity' => 1],
    ],
]);

$order->items; // Collection of OrderItemDto instances
```

Supported types:
- **Dt0 classes** — Each element cast via `Dt0::from()`
- **Enums** — Each element cast to the enum
- **Scalars** — `int`, `float`, `bool`, `string`

## Compatibility

| PHP | Laravel | Status |
|-----|---------|--------|
| 8.1 | 10.x | Supported |
| 8.2 | 10.x, 11.x | Supported |
| 8.3 | 10.x, 11.x, 12.x | Supported |
| 8.4 | 11.x, 12.x | Supported |

## Contributing

Contributions are welcome! Please feel free to:
- Open issues for bugs or feature requests
- Submit pull requests
- Improve documentation

## License

Laravel Dt0 is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
