<?php

namespace Rehark\ApiGeneratorBundle\Tests\Utils;

use ReflectionClass;
use ReflectionMethod;
use ReflectionObject;
use ReflectionProperty;

class PrivateAccessor
{

    /**
     * @param class-string $class
     * @param string $name
     */
    public static function getMethod(
        string $class,
        string $name
    ): ReflectionMethod {
        $class = new ReflectionClass($class);
        $method = $class->getMethod($name);
        return $method;
    }

    // private function callMethod(
    //     object $obj,
    //     string $method,
    //     array $args = []
    // ): mixed {
    //     $ref = PrivateAccessor::getMethod(get_class($obj), $method);
    //     return $ref->invoke($obj, ...$args);
    // }

    public static function getPropertyReference(
        object $instance,
        string $name
    ): ReflectionProperty {
        $reflection = new ReflectionObject($instance);
        $property = $reflection->getProperty($name);
        $property->setAccessible(true);
        return $property;
    }

    public static function getPropertyValue(
        object $instance,
        string $name
    ): mixed {
        $reflection = new ReflectionObject($instance);
        $property = $reflection->getProperty($name);
        $property->setAccessible(true);
        return $property->getValue($instance);
    }
}