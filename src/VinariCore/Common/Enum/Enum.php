<?php

/**
 * Nice implementation of Enumerators in PHP courtesy of @Dan on StackOverflow, originally posted to
 * http://stackoverflow.com/a/17045081/2257060
 */

namespace VinariCore\Common\Enum;

use VinariCore\Exception\NotSupportedException;
use ReflectionClass;

abstract class Enum
{

    const NONE = null;

    final private function __construct()
    {
        throw new NotSupportedException();
    }

    final private function __clone()
    {
        throw new NotSupportedException();
    }

    final public static function toArray()
    {
        return (new ReflectionClass(static::class))->getConstants();
    }

    final public static function isValid($value)
    {
        return in_array($value, static::toArray(), true);
    }

}
