<?php

namespace App\Api\Permission{{NAMESPACE}};

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Permission query for {{NAME}} entity
 * Used to filter GET results based on user roles or permissions
 */
class {{NAME}}PermissionQuery
{
    // Add your permission logic here
    public function apply(QueryBuilder &$qb, ?UserInterface $user): void {
        
    }

}
