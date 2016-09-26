<?php
/**
 * NotSupportedException, used to indicate that the expected behaviour will _never_ be supported by this code.
 */

namespace VinariCore\Exception;

class NotSupportedException extends \BadMethodCallException implements ExceptionInterface
{
    // …
}
