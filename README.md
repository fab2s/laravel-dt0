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

> **Note:** This package extends [fab2s/dt0](https://github.com/fab2s/dt0) with Laravel-specific features (validation, Eloquent casting). All features from the base package, including [property casting](https://github.com/fab2s/dt0/blob/1.0.0/docs/casters.md), property renaming, default values, output filtering, and more, work seamlessly here. Visit the [dt0 documentation](https://github.com/fab2s/dt0) for the complete feature set.

**Flexible, not dogmatic.** While immutability is the core feature, Dt0 doesn't force it. Use mutable properties when needed. Expose protected properties via `with()`. The package provides capabilities; you decide how to use them.

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
  - [Rule Priority](#rule-priority)
  - [Triggering Validation](#triggering-validation)
  - [Custom Validation Rules](#custom-validation-rules)
- [Model Attribute Casting](#model-attribute-casting)
- [Casters](#casters)
  - [Built-in Casters](#built-in-casters)
  - [CollectionOfCaster](#collectionofcaster)
  - [EncryptedCaster](#encryptedcaster)
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

> **Note:** This package uses Laravel's `Validator` under the hood. All [Laravel validation rules](https://laravel.com/docs/master/validation#available-validation-rules), [custom rule objects](https://laravel.com/docs/master/validation#custom-validation-rules), and [error message customization](https://laravel.com/docs/master/validation#customizing-the-error-messages) work exactly as documented in Laravel.

### Defining Rules

Rules can be defined at three levels:

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

**When to use each approach:**

| Approach | Best for |
|----------|----------|
| Property `#[Rule]` | Keeping rules close to properties, self-documenting DTOs |
| Class `#[Rules]` | Grouping rules together, inherited properties |
| `#[Validate]` Rules | Default/fallback rules that subclasses can override |

### Rule Priority

When the same property has rules defined at multiple levels, **only the highest priority rule applies** — rules are not merged.

**Priority order:** Property `#[Rule]` > Class `#[Rules]` > `#[Validate]` Rules

```php
#[Validate(
    Validator::class,
    new Rules(name: new Rule('min:100')),  // Lowest priority
)]
#[Rules(name: new Rule('min:50'))]          // Middle priority
class UserDto extends Dt0
{
    #[Rule('min:5')]  // Highest priority — only min:5 is applied
    public readonly string $name;
}

// Validates with min:5, NOT min:50 or min:100
$dto = UserDto::withValidation(name: 'hello'); // OK (5 chars)
```

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

Casters transform property values during hydration (input) and serialization (output). The `#[Cast]` attribute supports `in:`, `out:`, and `both:` parameters:

```php
// Same caster for both directions
#[Cast(both: new EncryptedCaster)]

// Different casters per direction
#[Cast(in: new DateTimeCaster, out: new DateTimeFormatCaster('Y-m-d'))]

// Combine both: with in: or out: — chained as a CasterCollection (onion ordering)
// Input runs: both → in | Output runs: out → both
#[Cast(both: new EncryptedCaster, in: new SomeSanitizer)]
```

### Built-in Casters

From [`fab2s/dt0`](https://github.com/fab2s/dt0/blob/1.0.0/docs/casters.md):

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

### EncryptedCaster

Encrypt/decrypt property values using Laravel's encryption:

```php
use fab2s\Dt0\Attribute\Cast;
use fab2s\Dt0\Laravel\Caster\EncryptedCaster;
use fab2s\Dt0\Laravel\Dt0;

class UserDto extends Dt0
{
    public readonly string $name;

    #[Cast(both: new EncryptedCaster)]
    public readonly string $apiKey;
}

// Initialize with plaintext — auto-detected and passed through
$user = UserDto::from([
    'name' => 'John',
    'apiKey' => 'my-secret-key',
]);

// Or load from encrypted storage — auto-detected and decrypted
$user = UserDto::from([
    'name' => 'John',
    'apiKey' => $encryptedValue,
]);

$user->apiKey;      // Plaintext value
$user->toArray();   // ['name' => 'John', 'apiKey' => '...encrypted...']
```

**Auto-detection:** On input, the caster automatically detects Laravel's encrypted payload format. Encrypted values are decrypted, while plaintext strings, arrays, and objects pass through unchanged. This allows flexible initialization from both plaintext and encrypted sources.

**Options:**

```php
// Serialize complex values (arrays, objects)
new EncryptedCaster(serialize: true)

// Use a custom encryption key (defaults to APP_KEY)
new EncryptedCaster(key: 'base64:...')

// Custom key with specific cipher
new EncryptedCaster(key: 'base64:...', cipher: 'AES-128-CBC')

// Reference a config key instead of hardcoding the key in the DTO
new EncryptedCaster(key: 'config:services.payment.encryption_key')
```

**Config key reference:** Use the `config:` prefix to reference a Laravel config path instead of hardcoding the encryption key. The value at that config path is resolved at runtime, and supports `base64:`-encoded keys just like a direct key would.

**Performance:** `Encrypter` instances are statically cached by key and cipher combination. Multiple DTO instances or properties using the same encryption key share a single `Encrypter`, avoiding repeated instantiation overhead.

**Stack trace safety:** On PHP 8.2+, all sensitive parameters (keys, plaintext values) are annotated with `#[\SensitiveParameter]` and redacted from exception stack traces. On PHP 8.1, the attribute is silently ignored.

**Eloquent model safety:** When using an `EncryptedCaster` DTO as an [Eloquent model attribute](#model-attribute-casting), calling `$model->toArray()` or `$model->toJson()` will trigger the DTO's output casters, meaning encrypted fields are **always encrypted** in the serialized output. Plaintext is only accessible through direct property access on the DTO instance (`$model->myDto->apiKey`).

```php
class SecureModel extends Model
{
    protected $casts = [
        'credentials' => CredentialsDto::class,
    ];
}

$model->credentials->apiKey;     // Plaintext (direct access)
$model->toArray()['credentials'] // ['apiKey' => '...encrypted...']
```

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
