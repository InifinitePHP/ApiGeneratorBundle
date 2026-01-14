<?php

namespace Rehark\ApiGeneratorBundle\Core\Actions;

enum CoreApiAction: string implements ApiActionsInterface
{
    case LIST   = 'list';
    case SHOW   = 'show';
    case CREATE = 'create';
    case UPDATE = 'update';
    case DELETE = 'delete';

    public function Action(): ApiAction
    {
        return match ($this) {
            self::LIST   => new ApiAction('list', ['GET'], ''),
            self::SHOW   => new ApiAction('show', ['GET'], '/{id}'),
            self::CREATE => new ApiAction('create', ['POST'], ''),
            self::UPDATE => new ApiAction('update', ['PATCH'], '/{id}'),
            self::DELETE => new ApiAction('delete', ['DELETE'], '/{id}'),
        };
    }
}
