<?php

namespace Rehark\ApiGeneratorBundle\Core\Controller;

use Rehark\ApiGeneratorBundle\Core\Actions\ApiAction;

interface ApiControllerInterface {
    /**
     * @return array<int, ApiAction>
     */
    public static function actions(): array;
}