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
     * Use with \OutOfBoundsException
     */
//    function UndefinedIndex(string $i) : array { return "Undefined index: $i"; }// почему тут string index, а ниже он нетипизирован?

    /**
     * Use with \OutOfRangeException
     */
  //  function InvalidIndex($i) : array { return 'Invalid index: '.(null === $i || is_scalar($i) ? var_export($i, true) : gettype($i)); }
}
