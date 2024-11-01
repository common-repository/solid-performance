<?php
/**
 * @license MIT
 *
 * Modified using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace SolidWP\Performance\Illuminate\Contracts\Support;

interface Arrayable
{
    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray();
}
