<?php

namespace App\Api\Controller{{NAMESPACE}};

use Rehark\ApiGeneratorBundle\Core\Actions\CoreApiAction;
use Rehark\ApiGeneratorBundle\Core\Controller\ApiController;

class {{NAME}}Controller extends ApiController
{

    public static string $version = 'v1';
    public static string $route = '{{ROUTE}}';

    protected function getEntityClass(): string
    {
        return \App\Entity\{{ENTITY}}::class;
    }

    public static function actions(): array
    {
        return [
            CoreApiAction::LIST->Action(),
            CoreApiAction::SHOW->Action(),
            CoreApiAction::CREATE->Action(),
            CoreApiAction::UPDATE->Action(),
            CoreApiAction::DELETE->Action()
        ];
    }
}