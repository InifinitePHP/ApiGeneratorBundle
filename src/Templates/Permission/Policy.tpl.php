<?php

namespace App\Api\Permission{{NAMESPACE}};

use App\Entity\{{ENTITY}};
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Rehark\ApiGeneratorBundle\Core\Security\Policy;

class {{NAME}}Policy extends Policy
{

    public function canList(
        ?UserInterface $user,
        ?Vote $vote
    ): bool {
        return false;
    }

    public function canCreate(
        ?UserInterface $user,
        ?Vote $vote
    ): bool {
        return false;
    }

    public function canRead(
        {{NAME}} ${{VAR}},
        ?UserInterface $user,
        ?Vote $vote
    ): bool {
        return false;
    }

    public function canUpdate(
        {{NAME}} ${{VAR}},
        ?UserInterface $user,
        ?Vote $vote
    ): bool {
        return false;
    }

    public function canDelete(
        {{NAME}} ${{VAR}},
        ?UserInterface $user,
        ?Vote $vote
    ): bool {
        return false;
    }
}