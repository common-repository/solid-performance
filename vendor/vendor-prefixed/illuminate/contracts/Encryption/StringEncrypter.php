<?php
/**
 * @license MIT
 *
 * Modified using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace SolidWP\Performance\Illuminate\Contracts\Encryption;

interface StringEncrypter
{
    /**
     * Encrypt a string without serialization.
     *
     * @param  string  $value
     * @return string
     *
     * @throws \SolidWP\Performance\Illuminate\Contracts\Encryption\EncryptException
     */
    public function encryptString($value);

    /**
     * Decrypt the given string without unserialization.
     *
     * @param  string  $payload
     * @return string
     *
     * @throws \SolidWP\Performance\Illuminate\Contracts\Encryption\DecryptException
     */
    public function decryptString($payload);
}
