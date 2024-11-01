<?php
/**
 * @license MIT
 *
 * Modified using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace SolidWP\Performance\Illuminate\Contracts\Filesystem;

interface Factory
{
    /**
     * Get a filesystem implementation.
     *
     * @param  string|null  $name
     * @return \SolidWP\Performance\Illuminate\Contracts\Filesystem\Filesystem
     */
    public function disk($name = null);
}
