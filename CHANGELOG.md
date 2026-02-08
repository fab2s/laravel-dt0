# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/), and this project adheres to [Semantic Versioning](https://semver.org/).

## [1.0.0] - 2025-02-08

First stable release, aligned with [fab2s/dt0 1.0.0](https://github.com/fab2s/dt0/releases/tag/1.0.0).

### Breaking Changes

#### Validation Priority Order Inverted (from dt0)

Property-level `#[Rule]` now takes precedence over class-level `#[Rules]`, which takes precedence over `#[Validate]` rules.

This allows class-level attributes to define defaults that individual properties can override — a more intuitive behavior.

**Priority order:** Property `#[Rule]` > Class `#[Rules]` > `#[Validate]` Rules

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
