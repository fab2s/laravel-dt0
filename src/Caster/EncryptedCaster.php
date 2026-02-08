<?php

declare(strict_types=1);

/*
 * This file is part of fab2s/laravel-dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/laravel-dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Laravel\Caster;

use fab2s\Dt0\Caster\CasterAbstract;
use fab2s\Dt0\Dt0;
use Illuminate\Encryption\Encrypter;
use SensitiveParameter;

class EncryptedCaster extends CasterAbstract
{
    /** @var array<string, Encrypter> */
    protected static array $encrypters = [];
    protected readonly Encrypter $encrypter;

    /**
     * @param bool        $serialize Whether to serialize/unserialize the value (for non-string data)
     * @param string|null $key       Custom encryption key (supports "base64:" and "config:" prefixes). Defaults to APP_KEY.
     * @param string|null $cipher    Cipher (supports "config:" prefix). Defaults to app cipher (usually AES-256-CBC).
     */
    public function __construct(
        public readonly bool $serialize = false,
        #[SensitiveParameter]
        ?string $key = null,
        #[SensitiveParameter]
        ?string $cipher = null,
    ) {
        /** @var string $configCipher */
        $configCipher = config('app.cipher', 'AES-256-CBC');
        $cipher       = $this->resolveConfig($cipher ?? $configCipher);
        /** @var string $configKey */
        $configKey    = config('app.key');
        $key          = $this->parseKey($key ?? $configKey);
        $encrypterKey = sha1($key . '|' . $cipher);
        static::$encrypters[$encrypterKey] ??= new Encrypter($key, $cipher);

        $this->encrypter = static::$encrypters[$encrypterKey];
    }

    public static function make(
        bool $serialize = false,
        #[SensitiveParameter]
        ?string $key = null,
        #[SensitiveParameter]
        ?string $cipher = null,
    ): static {
        return new static($serialize, $key, $cipher);
    }

    /**
     * On input ($data is array): decrypts the value if encrypted, otherwise passes through.
     * On output ($data is Dt0): encrypts the value.
     */
    public function cast(#[SensitiveParameter] mixed $value, #[SensitiveParameter] array|Dt0|null $data = null): mixed
    {
        if ($value === null) {
            return null;
        }

        // Output context: encrypt
        if ($data instanceof Dt0) {
            return $this->encrypt($value);
        }

        // Input context: decrypt if encrypted string
        if (! is_string($value)) {
            // Non-string values pass through for serialize case
            return $value;
        }

        return $this->isEncrypted($value) ? $this->decrypt($value) : $value;
    }

    protected function isEncrypted(#[SensitiveParameter] string $value): bool
    {
        $decoded = base64_decode($value, true);
        if ($decoded === false) {
            return false;
        }

        $payload = json_decode($decoded, true);

        return is_array($payload) && isset($payload['iv'], $payload['value'], $payload['mac']);
    }

    protected function encrypt(#[SensitiveParameter] mixed $value): string
    {
        return $this->serialize
            ? $this->encrypter->encrypt($value)
            : $this->encrypter->encryptString((string) $value); // @phpstan-ignore cast.string
    }

    protected function decrypt(#[SensitiveParameter] string $value): mixed
    {
        return $this->serialize
            ? $this->encrypter->decrypt($value)
            : $this->encrypter->decryptString($value);
    }

    protected function resolveConfig(string $value): string
    {
        if (str_starts_with($value, 'config:')) {
            /** @var string */
            return config(substr($value, 7));
        }

        return $value;
    }

    protected function parseKey(#[SensitiveParameter] string $key): string
    {
        $key = $this->resolveConfig($key);

        if (str_starts_with($key, 'base64:')) {
            return base64_decode(substr($key, 7));
        }

        return $key;
    }
}
