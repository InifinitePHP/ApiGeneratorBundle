<?php

namespace Rehark\ApiGeneratorBundle\Tests\Core\Exception;

use PHPUnit\Framework\TestCase;
use Rehark\ApiGeneratorBundle\Core\Exception\MethodException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class MethodExceptionTest extends TestCase
{
    public function testItExtendsBadRequestHttpException(): void
    {
        $exception = new MethodException(
            'Error on {class}::{method}',
            self::class,
            'someMethod'
        );

        self::assertInstanceOf(BadRequestHttpException::class, $exception);
    }

    public function testItBuildsMessageFromClassString(): void
    {
        $message = 'Invalid call {class}::{method}';
        $class   = 'App\\Entity\\User';
        $method  = 'getEmail';

        $exception = new MethodException($message, $class, $method);

        self::assertSame(
            'Invalid call App\\Entity\\User::getEmail',
            $exception->getMessage()
        );
    }

    public function testItBuildsMessageFromObject(): void
    {
        $message = 'Invalid call {class}::{method}';
        $object  = new class() {
        };
        $method  = 'doSomething';

        $exception = new MethodException($message, $object, $method);

        self::assertSame(
            'Invalid call '.get_class($object).'::doSomething',
            $exception->getMessage()
        );
    }

    public function testPlaceholdersAreOptional(): void
    {
        $message = 'Static error message';
        $class   = 'Whatever';
        $method  = 'whatever';

        $exception = new MethodException($message, $class, $method);

        self::assertSame('Static error message', $exception->getMessage());
    }

    public function testHttpStatusCodeIs400(): void
    {
        $exception = new MethodException(
            'Error on {class}::{method}',
            self::class,
            'someMethod'
        );

        self::assertSame(400, $exception->getStatusCode());
    }
}
