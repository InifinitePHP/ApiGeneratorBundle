<?php

namespace Rehark\ApiGeneratorBundle\Tests\Core\Security;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Rehark\ApiGeneratorBundle\Core\Security\Policy;
use Rehark\ApiGeneratorBundle\Tests\Utils\PrivateAccessor;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\User\UserInterface;

final class PolicyTest extends TestCase
{
    private EntityManagerInterface|\PHPUnit\Framework\MockObject\MockObject $em;
    private Policy $policy;

    protected function setUp(): void
    {
        $this->em = $this->createStub(EntityManagerInterface::class);
        $this->policy = new class($this->em) extends Policy {};
    }

    public function testIsAuthReturnsFalseForNullUser(): void
    {
        $vote = new Vote();

        $method = PrivateAccessor::getMethod(Policy::class, 'isAuth');
        $result = $method->invoke($this->policy, null, $vote);

        self::assertFalse($result);
    }

    public function testIsAuthReturnsFalseForNullUserWithoutVote(): void
    {
        $method = PrivateAccessor::getMethod(Policy::class, 'isAuth');
        $result = $method->invoke($this->policy, null, null);

        self::assertFalse($result);
    }

    public function testIsAuthReturnsTrueForAuthenticatedUser(): void
    {
        $user = $this->createStub(UserInterface::class);
        $vote = new Vote();
        
        $method = PrivateAccessor::getMethod(Policy::class, 'isAuth');
        $result = $method->invoke($this->policy, $user, $vote);

        self::assertTrue($result);
    }
}
