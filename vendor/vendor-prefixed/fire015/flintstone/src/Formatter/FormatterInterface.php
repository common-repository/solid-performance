<?php
/**
 * @license MIT
 *
 * Modified using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace SolidWP\Performance\Flintstone\Formatter;

interface FormatterInterface
{
    /**
     * Encode data into a string.
     *
     * @param mixed $data
     *
     * @return string
     */
    public function encode($data): string;

    /**
     * Decode a string into data.
     *
     * @param string $data
     *
     * @return mixed
     */
    public function decode(string $data);
}
