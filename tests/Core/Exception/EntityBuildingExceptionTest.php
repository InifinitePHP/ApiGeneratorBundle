<?php

namespace Rehark\ApiGeneratorBundle\Tests\Core\Exception;

use PHPUnit\Framework\TestCase;
use Rehark\ApiGeneratorBundle\Core\Exception\EntityBuildingException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class EntityBuildingExceptionTest extends TestCase
{
    public function testItExtendsBadRequestHttpException(): void
    {
        $exception = new EntityBuildingException('Error', []);

        self::assertInstanceOf(BadRequestHttpException::class, $exception);
    }

    public function testItBuildsMessageWithTrace(): void
    {
        $message = 'Entity build failed';
        $trace   = ['User', 'Address', 'Street'];

        $exception = new EntityBuildingException($message, $trace);

        self::assertSame(
            'Entity build failed User->Address->Street',
            $exception->getMessage()
        );
    }

    public function testItHandlesEmptyTrace(): void
    {
        $message = 'Entity build failed';
        $trace   = [];

        $exception = new EntityBuildingException($message, $trace);

        self::assertSame('Entity build failed ', $exception->getMessage());
    }

    public function testHttpStatusCodeIs400(): void
    {
        $exception = new EntityBuildingException('Error', []);

        self::assertSame(400, $exception->getStatusCode());
    }
}
