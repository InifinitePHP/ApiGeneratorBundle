<?php

namespace Rehark\ApiGeneratorBundle\Core\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class MethodException extends BadRequestHttpException {

    public function __construct(
        String $message,
        Object|string $class_or_object,
        string $method
    ) {
        $class = !is_string($class_or_object) ? get_class($class_or_object) : $class_or_object;
        $output = str_replace(['{class}', '{method}'], [$class, $method], $message);
        parent::__construct($output);
    }
}