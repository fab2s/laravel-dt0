# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/), and this project adheres to [Semantic Versioning](https://semver.org/).

## [1.0.1] - 2026-02-16

### Added

- Artisan `make:dt0` generator command with `--validated` option
- `Dt0ServiceProvider` with Laravel auto-discovery
- Publishable stubs (`dt0.stub`, `dt0.validated.stub`)

## [1.0.0] - 2025-02-08

First stable release, aligned with [fab2s/dt0 1.0.0](https://github.com/fab2s/dt0/releases/tag/1.0.0).

### Breaking Changes

#### Inherited from [fab2s/dt0](https://github.com/fab2s/dt0)

- **Priority Order Inverted:** Both casting and validation priority are now Property `#[Rule]`/`#[Cast]` > Class `#[Rules]`/`#[Casts]` > `#[Validate]` rules. This allows class-level attributes to define defaults that individual properties can override.
- **`#[Cast]` Attribute Signature:** The `#[Cast]` attribute now accepts a `both` parameter (third positional argument) for bidirectional casters. This shifts the position of `default`, `renameFrom`, `renameTo`, and `propName`. Users relying on positional arguments should switch to named arguments.
- **`ClassCaster` Strict Types:** `ClassCaster` now enforces strict types — passing a scalar value whose type doesn't match the target class constructor will throw a `TypeError` instead of silently coercing.
- **Output Renaming Consistency:** `toJsonArray()` now applies `renameTo` consistently with `toArray()`.

See the [dt0 changelog](https://github.com/fab2s/dt0/blob/main/CHANGELOG.md) for the complete list.

### Added

#### EncryptedCaster

Encrypt/decrypt property values using Laravel's encryption. Supports custom encryption keys.

```php
#[Cast(in: new EncryptedCaster, out: new EncryptedCaster)]
public readonly string $secret;
```

### Changed

- Enforce `declare(strict_types=1)` across all source files (except `Dt0` for consistency with `BaseDt0`)
- Add explicit type hints to `Dt0Cast::get()` and `Dt0Cast::set()` to match `CastsAttributes` interface
- PHPStan level 9 compliance for both `src` and `tests`

## [0.0.1] - 2024-04-28

Initial release.
