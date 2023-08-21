<?php

namespace MaxieSystems\Exception;

abstract class Messages
{
    /**
     * Use with \Error
     */
    public static function readonlyObject(object $obj): string
    {
        return 'Cannot modify readonly object of class ' . $obj::class;
    }

    /**
     * Use with \Error
     */
    public static function readonlyProperty(object $obj, string $property): string
    {
        return 'Cannot modify readonly property ' . $obj::class . '::$' . $property;
    }

    /**
     * Use with \Error
     */
    public static function undefinedProperty(object $obj, string $property): string
    {
        return 'Undefined property: ' . $obj::class . '::$' . $property;
    }

    /**
     * Use with \Error
     */
    public static function uninitializedProperty(object $obj, string $property): string
    {
        return 'Property ' . $obj::class . '::$' . $property . ' must not be accessed before initialization';
    }

    /**
     * Use with \Error
     */
    public static function undefinedMethod(string $class, string $name): string
    {
        return 'Call to undefined method ' . $class . '::' . $name . '()';
    }

    /**
     * Use with \TypeError
     */
    public static function illegalOffsetType(mixed $offset): string
    {
        $type = gettype($offset);
        return 'Illegal offset type: ' . ('object' === $type ? 'instance of ' . get_class($offset) : $type);
    }

    /**
     * Use with \OutOfBoundsException
     */
    public static function undefinedIndex(int|string $i): string
    {
        return "Undefined index: $i";
    }

    /**
     * Use with \OutOfRangeException
     */
    public static function invalidIndex(mixed $i): string
    {
        return 'Invalid index: ' . (null === $i || is_scalar($i) ? var_export($i, true) : gettype($i));
    }
}
