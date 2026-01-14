<?php

namespace Rehark\ApiGeneratorBundle\Core\Controller;

interface ApiControllerInterface {
    /**
     * @return array<int, ApiAction>
     */
    public static function actions(): array;
}