<?php

namespace Rehark\ApiGeneratorBundle\Tests\Utils;

use ReflectionClass;
use ReflectionObject;

class PrivateAccessor
{

    public static function getMethod(string $class, string $name)
    {
        $class = new ReflectionClass($class);
        $method = $class->getMethod($name);
        return $method;
    }

    private function callMethod(object $obj, string $method, array $args = [])
    {
        $ref = PrivateAccessor::getMethod(get_class($obj), $method);
        return $ref->invoke($obj, ...$args);
    }

    public static function getPropertyReference(mixed $instance, $name)
    {
        $reflection = new ReflectionObject($instance);
        $property = $reflection->getProperty($name);
        $property->setAccessible(true);
        return $property;
    }

    public static function getPropertyValue(mixed $instance, $name)
    {
        $reflection = new ReflectionObject($instance);
        $property = $reflection->getProperty($name);
        $property->setAccessible(true);
        return $property->getValue($instance);
    }
}