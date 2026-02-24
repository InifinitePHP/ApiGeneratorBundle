<?php

namespace App\Api\Controller{{NAMESPACE}};

use Rehark\ApiGeneratorBundle\Core\Actions\ApiAction;
use Rehark\ApiGeneratorBundle\Core\Controller\ApiController;
use App\Api\DTO{{NAMESPACE}}\Create{{NAME}}Input;
use App\Api\DTO{{NAMESPACE}}\Update{{NAME}}Input;
use App\Api\DTO{{NAMESPACE}}\{{NAME}}Output;

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
            new ApiAction('list', ['GET'], '', null, {{NAME}}Output::class),
            new ApiAction('show', ['GET'], '/{id}', null, {{NAME}}Output::class),
            new ApiAction('create', ['POST'], '', Create{{NAME}}Input::class, {{NAME}}Output::class),
            new ApiAction('update', ['PATCH'], '/{id}', Update{{NAME}}Input::class, {{NAME}}Output::class),
            new ApiAction('delete', ['DELETE'], '/{id}', null, {{NAME}}Output::class),
        ];
    }
}