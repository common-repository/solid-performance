<?php
/**
 * @license MIT
 *
 * Modified using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace SolidWP\Performance\Flintstone\Formatter;

use SolidWP\Performance\Flintstone\Exception;

class JsonFormatter implements FormatterInterface
{
    /**
     * @var bool
     */
    private $assoc;

    public function __construct(bool $assoc = true)
    {
        $this->assoc = $assoc;
    }

    /**
     * {@inheritdoc}
     */
    public function encode($data): string
    {
        $result = json_encode($data);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $result;
        }

        throw new Exception(json_last_error_msg());
    }

    /**
     * {@inheritdoc}
     */
    public function decode(string $data)
    {
        $result = json_decode($data, $this->assoc);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $result;
        }

        throw new Exception(json_last_error_msg());
    }
}
