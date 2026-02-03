<?php

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
use Illuminate\Support\Facades\Crypt;

class EncryptedCaster extends CasterAbstract
{
    protected ?Encrypter $encrypter = null;

    /**
     * @param bool        $serialize Whether to serialize/unserialize the value (for non-string data)
     * @param string|null $key       Custom encryption key (supports "base64:" prefix). Defaults to APP_KEY.
     * @param string|null $cipher    Cipher to use with custom key. Defaults to app cipher (usually AES-256-CBC).
     */
    public function __construct(
        public readonly bool $serialize = false,
        ?string $key = null,
        ?string $cipher = null,
    ) {
        if ($key !== null) {
            $this->encrypter = new Encrypter(
                $this->parseKey($key),
                $cipher ?? config('app.cipher', 'AES-256-CBC'),
            );
        }
    }

    public static function make(
        bool $serialize = false,
        ?string $key = null,
        ?string $cipher = null,
    ): static {
        return new static($serialize, $key, $cipher);
    }

    /**
     * On input ($data is array): decrypts the value if encrypted, otherwise passes through.
     * On output ($data is Dt0): encrypts the value.
     */
    public function cast(mixed $value, array|Dt0|null $data = null): mixed
    {
        if ($value === null) {
            return null;
        }

        // Output context: encrypt
        if ($data instanceof Dt0) {
            return $this->encrypt($value);
        }

        // Input context: decrypt if encrypted string, otherwise pass through
        if (! is_string($value)) {
            // Non-string values (arrays, objects) pass through for serialize case
            return $value;
        }

        return $this->isEncrypted($value) ? $this->decrypt($value) : $value;
    }

    /**
     * Check if a string value looks like a Laravel encrypted payload.
     */
    protected function isEncrypted(string $value): bool
    {
        $decoded = base64_decode($value, true);
        if ($decoded === false) {
            return false;
        }

        $payload = json_decode($decoded, true);

        return is_array($payload) && isset($payload['iv'], $payload['value'], $payload['mac']);
    }

    protected function encrypt(mixed $value): string
    {
        if ($this->encrypter) {
            return $this->serialize
                ? $this->encrypter->encrypt($value)
                : $this->encrypter->encryptString((string) $value);
        }

        return $this->serialize
            ? Crypt::encrypt($value)
            : Crypt::encryptString((string) $value);
    }

    protected function decrypt(string $value): mixed
    {
        if ($this->encrypter) {
            return $this->serialize
                ? $this->encrypter->decrypt($value)
                : $this->encrypter->decryptString($value);
        }

        return $this->serialize
            ? Crypt::decrypt($value)
            : Crypt::decryptString($value);
    }

    protected function parseKey(string $key): string
    {
        if (str_starts_with($key, 'base64:')) {
            return base64_decode(substr($key, 7));
        }

        return $key;
    }
}
