# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/), and this project adheres to [Semantic Versioning](https://semver.org/).

## [2.0.0] - 2026-07-15

### Breaking Changes

#### Minimum PHP 8.2

Dropped PHP 8.1 support — the minimum is now PHP 8.2, aligned with [fab2s/dt0 2.0.0](https://github.com/fab2s/dt0/releases/tag/2.0.0). Projects still on PHP 8.1 can continue using the 1.0.x line.

#### Laravel 10 support dropped

Dropped Laravel 10 (`illuminate/*` and `orchestra/testbench ^8.0`). The supported range is now Laravel 11, 12, and 13. Projects still on Laravel 10 can continue using the 1.0.x line.

### Added

#### PHP 8.5 support

Full support for PHP 8.5, including its stricter runtime checks.

#### Laravel 13 support

Support for Laravel 13 (`illuminate/* ^13.0`, `orchestra/testbench ^11.0`) alongside Laravel 11 and 12. The CI matrix now covers PHP 8.2–8.5 across Laravel 11/12/13, and QA/coverage runs on PHP 8.5.

### Changed

- Bumped `fab2s/dt0` to `^2.0.0`.
- Bumped dev dependencies: `phpunit/phpunit` to `^11.0|^12.0`, `orchestra/testbench` to `^9.0|^10.0|^11.0`.

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
