<?php
/**
 * @license MIT
 *
 * Modified using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace SolidWP\Performance\Illuminate\Contracts\Cache;

interface LockProvider
{
    /**
     * Get a lock instance.
     *
     * @param  string  $name
     * @param  int  $seconds
     * @param  string|null  $owner
     * @return \SolidWP\Performance\Illuminate\Contracts\Cache\Lock
     */
    public function lock($name, $seconds = 0, $owner = null);

    /**
     * Restore a lock instance using the owner identifier.
     *
     * @param  string  $name
     * @param  string  $owner
     * @return \SolidWP\Performance\Illuminate\Contracts\Cache\Lock
     */
    public function restoreLock($name, $owner);
}
