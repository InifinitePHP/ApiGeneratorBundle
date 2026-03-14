<?php

namespace Rehark\ApiGeneratorBundle\Tests\Core\DTO;

use PHPUnit\Framework\TestCase;
use Rehark\ApiGeneratorBundle\Core\DTO\DefaultOutputDto;

final class DefaultOutputDtoTest extends TestCase
{
    private DefaultOutputDto $dto;

    protected function setUp(): void
    {
        $this->dto = new DefaultOutputDto();
    }

    public function testDynamicSetGet(): void
    {
        $this->dto->name     = 'John';
        $this->dto->age      = 30;
        $this->dto->active   = true;
        $this->dto->scores   = [95, 87, 92];

        self::assertSame('John', $this->dto->name);
        self::assertSame(30, $this->dto->age);
        self::assertTrue($this->dto->active);
        self::assertSame([95, 87, 92], $this->dto->scores);
    }

    public function testDynamicPropertiesPersist(): void
    {
        $dto1 = new DefaultOutputDto();
        $dto1->id = 42;

        $dto2 = new DefaultOutputDto();
        $dto2->id = 123;

        self::assertSame(42, $dto1->id);
        self::assertSame(123, $dto2->id);
    }

    public function testUndefinedPropertyDoesNotExist(): void
    {
        self::assertFalse(property_exists($this->dto, 'nonExistent'));
        self::assertFalse(isset($this->dto->nonExistent)); // Si __isset implémenté
        
        $value = @$this->dto->nonExistent;
        self::assertNull($value);
    }


    public function testSetGetMixedTypes(): void
    {
        $this->dto->string  = 'hello';
        $this->dto->int     = 42;
        $this->dto->float   = 3.14;
        $this->dto->bool    = false;
        $this->dto->array   = ['a', 'b'];
        $this->dto->null    = null;
        $this->dto->object  = new \stdClass();

        self::assertSame('hello', $this->dto->string);
        self::assertSame(42, $this->dto->int);
        self::assertSame(3.14, $this->dto->float);
        self::assertFalse($this->dto->bool);
        self::assertSame(['a', 'b'], $this->dto->array);
        self::assertNull($this->dto->null);
        self::assertInstanceOf(\stdClass::class, $this->dto->object);
    }

    public function testIssetAndUnsetWork(): void
    {
        $this->dto->exists = 'value';
        self::assertSame('value', $this->dto->exists);

        $this->dto->exists = null;
        self::assertNull($this->dto->exists);

        unset($this->dto->exists);
        $value = @$this->dto->exists;
        self::assertNull($value);
    }

    public function testAllowDynamicPropertiesAttribute(): void
    {
        $ref = new \ReflectionClass(DefaultOutputDto::class);
        $attr = $ref->getAttributes(\AllowDynamicProperties::class);
        
        self::assertCount(1, $attr);
        self::assertInstanceOf(\AllowDynamicProperties::class, $attr[0]->newInstance());
    }
}
