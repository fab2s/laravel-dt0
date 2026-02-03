<?php

/*
 * This file is part of fab2s/laravel-dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/laravel-dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Laravel\Tests;

use fab2s\Dt0\Laravel\Caster\EncryptedCaster;
use fab2s\Dt0\Laravel\Tests\Artifacts\EncryptedDt0;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

class EncryptedCasterTest extends TestCase
{
    public function test_decrypts_on_input(): void
    {
        $encrypted = Crypt::encryptString('my-secret-value');

        $dt0 = EncryptedDt0::from(['secret' => $encrypted]);

        $this->assertSame('my-secret-value', $dt0->secret);
    }

    public function test_encrypts_on_output(): void
    {
        $encrypted = Crypt::encryptString('my-secret-value');
        $dt0       = EncryptedDt0::from(['secret' => $encrypted]);

        $output = $dt0->toArray();

        $this->assertNotSame('my-secret-value', $output['secret']);
        $this->assertSame('my-secret-value', Crypt::decryptString($output['secret']));
    }

    public function test_roundtrip(): void
    {
        $encrypted = Crypt::encryptString('sensitive-data');
        $original  = EncryptedDt0::from(['secret' => $encrypted]);

        $serialized = $original->toArray();
        $restored   = EncryptedDt0::from($serialized);

        $this->assertSame('sensitive-data', $restored->secret);
    }

    public function test_null_value(): void
    {
        $dt0 = EncryptedDt0::from(['secret' => null]);

        $this->assertNull($dt0->secret);
        $this->assertNull($dt0->toArray()['secret']);
    }

    public function test_custom_key_encrypts_differently(): void
    {
        $customKey = 'base64:' . base64_encode(random_bytes(32));

        $caster = new EncryptedCaster(key: $customKey);

        // Create a mock Dt0 for output context
        $encrypted = Crypt::encryptString('test');
        $dt0       = EncryptedDt0::from(['secret' => $encrypted]);

        // Encrypt with custom key (output context)
        $encryptedWithCustomKey = $caster->cast('custom-key-secret', $dt0);

        // Should not be decryptable with default APP_KEY
        $this->expectException(DecryptException::class);
        Crypt::decryptString($encryptedWithCustomKey);
    }

    public function test_custom_key_roundtrip(): void
    {
        $customKey = 'base64:' . base64_encode(random_bytes(32));

        $caster = new EncryptedCaster(key: $customKey);

        // Create a mock Dt0 for output context
        $encrypted = Crypt::encryptString('test');
        $dt0       = EncryptedDt0::from(['secret' => $encrypted]);

        // Encrypt (output context)
        $encryptedValue = $caster->cast('roundtrip-value', $dt0);

        // Decrypt (input context)
        $decrypted = $caster->cast($encryptedValue, []);

        $this->assertSame('roundtrip-value', $decrypted);
    }

    public function test_serialize_array_roundtrip(): void
    {
        $caster = new EncryptedCaster(serialize: true);

        // Create a mock Dt0 for output context
        $encrypted = Crypt::encryptString('test');
        $dt0       = EncryptedDt0::from(['secret' => $encrypted]);

        $originalArray = ['foo' => 'bar', 'nested' => ['key' => 'value']];

        // Encrypt (output context)
        $encryptedValue = $caster->cast($originalArray, $dt0);

        // Decrypt (input context)
        $decrypted = $caster->cast($encryptedValue, []);

        $this->assertSame($originalArray, $decrypted);
    }

    public function test_serialize_with_crypt_facade(): void
    {
        $caster = new EncryptedCaster(serialize: true);

        // Create a mock Dt0 for output context
        $encrypted = Crypt::encryptString('test');
        $dt0       = EncryptedDt0::from(['secret' => $encrypted]);

        $originalArray = ['key' => 'value'];

        // Encrypt with caster
        $encryptedValue = $caster->cast($originalArray, $dt0);

        // Should be decryptable with Crypt::decrypt (not decryptString)
        $decrypted = Crypt::decrypt($encryptedValue);

        $this->assertSame($originalArray, $decrypted);
    }

    public function test_serialize_with_custom_key(): void
    {
        $customKey = 'base64:' . base64_encode(random_bytes(32));

        $caster = new EncryptedCaster(serialize: true, key: $customKey);

        // Create a mock Dt0 for output context
        $encrypted = Crypt::encryptString('test');
        $dt0       = EncryptedDt0::from(['secret' => $encrypted]);

        $originalArray = ['sensitive' => 'data', 'count' => 42];

        // Encrypt (output context)
        $encryptedValue = $caster->cast($originalArray, $dt0);

        // Decrypt (input context)
        $decrypted = $caster->cast($encryptedValue, []);

        $this->assertSame($originalArray, $decrypted);
    }

    public function test_raw_key_with_make(): void
    {
        // Raw 32-byte key (not base64 encoded)
        $rawKey = random_bytes(32);

        $caster = EncryptedCaster::make(key: $rawKey);

        // Create a mock Dt0 for output context
        $encrypted = Crypt::encryptString('test');
        $dt0       = EncryptedDt0::from(['secret' => $encrypted]);

        // Encrypt (output context)
        $encryptedValue = $caster->cast('raw-key-secret', $dt0);

        // Decrypt (input context)
        $decrypted = $caster->cast($encryptedValue, []);

        $this->assertSame('raw-key-secret', $decrypted);
    }

    public function test_non_string_input_passes_through(): void
    {
        $caster = EncryptedCaster::make();

        // Non-string values pass through on input (for serialize case)
        $this->assertSame(123, $caster->cast(123, []));
        $this->assertSame(['array'], $caster->cast(['array'], []));
        $this->assertSame(true, $caster->cast(true, []));
    }

    public function test_plaintext_string_passes_through(): void
    {
        $caster = EncryptedCaster::make();

        // Non-encrypted strings pass through
        $this->assertSame('plaintext', $caster->cast('plaintext', []));
        $this->assertSame('hello world', $caster->cast('hello world', []));
    }

    public function test_plaintext_initialization_and_roundtrip(): void
    {
        $encrypted = Crypt::encryptString('initial');
        $dt0       = EncryptedDt0::from(['secret' => $encrypted]);

        // Can initialize with plaintext (passes through)
        $plaintextDt0 = EncryptedDt0::from(['secret' => 'my-plaintext-secret']);
        $this->assertSame('my-plaintext-secret', $plaintextDt0->secret);

        // Output encrypts it
        $output = $plaintextDt0->toArray();
        $this->assertNotSame('my-plaintext-secret', $output['secret']);

        // Can decrypt the output
        $this->assertSame('my-plaintext-secret', Crypt::decryptString($output['secret']));
    }
}
