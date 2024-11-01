<?php
/**
 * @license MIT
 *
 * Modified using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace SolidWP\Performance\Illuminate\Contracts\Validation;

interface UncompromisedVerifier
{
    /**
     * Verify that the given data has not been compromised in data leaks.
     *
     * @param  array  $data
     * @return bool
     */
    public function verify($data);
}
