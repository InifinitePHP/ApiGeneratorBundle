<?php

namespace App\Api\Controller{{NAMESPACE}};

use Rehark\ApiGeneratorBundle\Core\Actions\ApiAction;
use Rehark\ApiGeneratorBundle\Core\Controller\ApiController;
use App\Api\DTO{{NAMESPACE}}\Search{{NAME}}Input;
use App\Api\DTO{{NAMESPACE}}\Create{{NAME}}Input;
use App\Api\DTO{{NAMESPACE}}\Update{{NAME}}Input;
use App\Api\DTO{{NAMESPACE}}\{{NAME}}Output;
use \App\Entity\{{ENTITY}};

class {{NAME}}Controller extends ApiController
{

    public static string $version = 'v1';
    public static string $route = '{{ROUTE}}';

    protected function getEntityClass(): string
    {
        return {{NAME}}::class;
    }

    public static function actions(): array
    {
        return [
            new ApiAction('search', ['POST'], '/search', Search{{NAME}}Input::class, {{NAME}}Output::class),
            new ApiAction('show', ['GET'], '/{id}', DefaultInputDto::class, {{NAME}}Output::class, [
                'id' => ['class' => {{NAME}}::class, 'property' => 'id', 'param' => 'entity']
            ]),
            new ApiAction('create', ['POST'], '', Create{{NAME}}Input::class, {{NAME}}Output::class),
            new ApiAction('update', ['PATCH'], '/{id}', Update{{NAME}}Input::class, {{NAME}}Output::class, [
                'id' => ['class' => {{NAME}}::class, 'property' => 'id', 'param' => 'entity']
            ]),
            new ApiAction('delete', ['DELETE'], '/{id}', DefaultInputDto::class, {{NAME}}Output::class, [
                'id' => ['class' => {{NAME}}::class, 'property' => 'id', 'param' => 'entity']
            ]),
        ];
    }
}