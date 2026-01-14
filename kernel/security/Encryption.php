<?php namespace Kernel\Security;

/**
 * Class Encryption
 *
 * Provides symmetric AES-256-CBC encryption and decryption using a derived key from APPLICATION_KEY.
 * Suitable for encrypting sensitive values that need to be decrypted within the backend.
 *
 * @package Kernel\Security
 */
class Encryption
{
    /**
     * Encrypts the given data using AES-256-CBC with a securely hashed key and a random IV.
     * The result is base64-encoded for safe storage (e.g. in database).
     *
     * @param string $data The plaintext data to encrypt.
     * @return string The base64-encoded encrypted string (IV + ciphertext).
     */
    public static function encode($data)
    {
        $key = hash('sha256', APPLICATION_KEY, true);
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypts the given base64-encoded string using AES-256-CBC.
     * Assumes the first 16 bytes are the IV and the rest is the ciphertext.
     *
     * @param string $data The base64-encoded string to decrypt (IV + ciphertext).
     * @return string|false The decrypted plaintext, or false on failure.
     */
    public static function decode($data)
    {
        $key = hash('sha256', APPLICATION_KEY, true);
        $decoded = base64_decode($data);
        $iv = substr($decoded, 0, 16);
        $ciphertext = substr($decoded, 16);
        return openssl_decrypt($ciphertext, 'AES-256-CBC', $key, 0, $iv);
    }
}
