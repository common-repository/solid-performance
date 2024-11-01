<?php
/**
 * @license MIT
 *
 * Modified using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace SolidWP\Performance\Illuminate\Contracts\Support;

use ArrayAccess;
use IteratorAggregate;

interface ValidatedData extends Arrayable, ArrayAccess, IteratorAggregate
{
    //
}
