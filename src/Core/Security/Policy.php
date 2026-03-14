<?php

namespace Rehark\ApiGeneratorBundle\Core\Security;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class Policy {

    public function __construct(
        private EntityManagerInterface $em
    ) {}

    protected function isAuth(
        ?UserInterface $user,
        ?Vote $vote
    ): bool {
        if ($user === null) {
            $vote?->addReason('The user is not logged in.');
            return false;
        }

        return true;
    }
}