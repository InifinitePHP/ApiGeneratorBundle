<?php

namespace App\Api\Permission{{NAMESPACE}};

use App\Entity\{{ENTITY}};
use App\Api\Permission{{NAMESPACE}}\{{NAME}}Policy;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class {{NAME}}Voter extends Voter
{
    const LIST  = '{{VAR}}.list';
    const CREATE = '{{VAR}}.create';
    const READ   = '{{VAR}}.read';
    const UPDATE = '{{VAR}}.update';
    const DELETE = '{{VAR}}.delete';

    public function __construct(
        private {{NAME}}Policy $policy
    ) {}

    protected function supports(string $attribute, mixed $subject): bool
    {

        if (!in_array($attribute, [self::LIST, self::CREATE, self::READ, self::UPDATE, self::DELETE])) {
            return false;
        }

        if (in_array($attribute, [self::LIST, self::CREATE])) {
            return true;
        }

        return $subject instanceof {{NAME}};
    }

    protected function voteOnAttribute(
        string $attribute,
        mixed ${{VAR}},
        TokenInterface $token,
        ?Vote $vote = null
    ): bool {

        $user = $token->getUser();

        // Determine whether the given user is allowed to view the subject.
        return match($attribute) {
            self::LIST => $this->policy->canList($user, $vote),
            self::CREATE => $this->policy->canCreate($user, $vote),
            self::READ => $this->policy->canRead(${{VAR}}, $user, $vote),
            self::UPDATE => $this->policy->canUpdate(${{VAR}}, $user, $vote),
            self::DELETE => $this->policy->canDelete(${{VAR}}, $user, $vote),
            default => throw new \LogicException('This code should not be reached!')
        };
    }

}