<?php

namespace Rehark\ApiGeneratorBundle\Core\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class EntityBuildingException extends BadRequestHttpException {

    /**
     * @param string $message
     * @param array<string> $trace
     */
    public function __construct(
        string $message,
        array $trace,
    ) {
        $output = $message . ' ' . join('->', $trace);
        parent::__construct($output);
    }
}