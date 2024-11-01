<?php
/**
 * @license MIT
 *
 * Modified using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace SolidWP\Performance\Illuminate\Contracts\Support;

interface DeferringDisplayableValue
{
    /**
     * Resolve the displayable value that the class is deferring.
     *
     * @return \SolidWP\Performance\Illuminate\Contracts\Support\Htmlable|string
     */
    public function resolveDisplayableValue();
}
