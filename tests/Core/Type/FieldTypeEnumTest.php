<?php

namespace Rehark\ApiGeneratorBundle\Tests\Core\Type;

use Doctrine\DBAL\Types\Types;
use PHPUnit\Framework\TestCase;
use Rehark\ApiGeneratorBundle\Core\Type\FieldTypeEnum;

final class FieldTypeEnumTest extends TestCase
{
    public function testAllStaticMethodsReturnCorrectCases(): void
    {
        self::assertEqualsCanonicalizing(
            [FieldTypeEnum::INTEGER, FieldTypeEnum::SMALLINT],
            FieldTypeEnum::integers()
        );

        self::assertEqualsCanonicalizing(
            [
                FieldTypeEnum::BIGINT,
                FieldTypeEnum::DECIMAL,
                FieldTypeEnum::STRING,
                FieldTypeEnum::ASCII_STRING,
                FieldTypeEnum::TEXT,
                FieldTypeEnum::GUID,
                FieldTypeEnum::ENUM
            ],
            FieldTypeEnum::strings()
        );

        self::assertEqualsCanonicalizing(
            FieldTypeEnum::passThrough(),
            [FieldTypeEnum::JSON, FieldTypeEnum::SIMPLE_ARRAY, FieldTypeEnum::BLOB, FieldTypeEnum::BINARY]
        );
    }

    public function testCastHandlesNull(): void
    {
        foreach (FieldTypeEnum::cases() as $type) {
            self::assertNull($type->cast(null));
        }
    }

    public function testCastPassThroughTypes(): void
    {
        $value = ['complex' => 'array'];
        
        foreach (FieldTypeEnum::passThrough() as $type) {
            self::assertSame($value, $type->cast($value));
        }
    }

    public function testCastIntegers(): void
    {
        foreach (FieldTypeEnum::integers() as $type) {
            self::assertSame(42, $type->cast('42'));
            self::assertSame(42, $type->cast(42.0));
            self::assertSame(-5, $type->cast('-5'));
        }
    }

    public function testCastStrings(): void
    {
        foreach (FieldTypeEnum::strings() as $type) {
            self::assertSame('hello', $type->cast('hello'));
            self::assertSame('42', $type->cast(42));
            self::assertNull($type->cast(null));
        }
    }

    public function testCastBoolean(): void
    {
        self::assertTrue(FieldTypeEnum::BOOLEAN->cast('1'));
        self::assertTrue(FieldTypeEnum::BOOLEAN->cast('true'));
        self::assertFalse(FieldTypeEnum::BOOLEAN->cast('0'));
        self::assertFalse(FieldTypeEnum::BOOLEAN->cast('false'));
    }

    public function testCastDates(): void
    {
        $dateStr = '2026-03-14 20:00:00';

        foreach (FieldTypeEnum::mutableDates() as $type) {
            $result = $type->cast($dateStr);
            self::assertInstanceOf(\DateTime::class, $result);
            self::assertSame($dateStr, $result->format('Y-m-d H:i:s'));
        }

        foreach (FieldTypeEnum::immutableDates() as $type) {
            $result = $type->cast($dateStr);
            self::assertInstanceOf(\DateTimeImmutable::class, $result);
            self::assertSame($dateStr, $result->format('Y-m-d H:i:s'));
        }
    }

    public function testCastDateInterval(): void
    {
        $interval = FieldTypeEnum::DATEINTERVAL->cast('PT1H');
        self::assertInstanceOf(\DateInterval::class, $interval);
        self::assertSame(1, $interval->h);
    }

    public function testFromDoctrineTypeValid(): void
    {
        self::assertSame(FieldTypeEnum::INTEGER, FieldTypeEnum::fromDoctrineType(Types::INTEGER));
        self::assertSame(FieldTypeEnum::JSON, FieldTypeEnum::fromDoctrineType(Types::JSON));
    }

    public function testFromDoctrineTypeInvalidThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Type Doctrine "unknown" non supporté par FieldTypeEnum.');

        FieldTypeEnum::fromDoctrineType('unknown');
    }
}
