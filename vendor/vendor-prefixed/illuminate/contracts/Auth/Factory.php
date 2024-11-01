<?php
/**
 * @license MIT
 *
 * Modified using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace SolidWP\Performance\Illuminate\Contracts\Auth;

interface Factory
{
    /**
     * Get a guard instance by name.
     *
     * @param  string|null  $name
     * @return \SolidWP\Performance\Illuminate\Contracts\Auth\Guard|\SolidWP\Performance\Illuminate\Contracts\Auth\StatefulGuard
     */
    public function guard($name = null);

    /**
     * Set the default guard the factory should serve.
     *
     * @param  string  $name
     * @return void
     */
    public function shouldUse($name);
}
