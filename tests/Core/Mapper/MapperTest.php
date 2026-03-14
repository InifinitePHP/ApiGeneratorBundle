<?php

namespace Rehark\ApiGeneratorBundle\Tests\Core\Mapper;

use PHPUnit\Framework\TestCase;
use Rehark\ApiGeneratorBundle\Core\Mapper\Mapper;

final class MapperTest extends TestCase
{
    public function testFromEntityMapsAllPublicProperties(): void
    {
        $entity = new DummyEntity(42, 'John');
        $dto    = new DummyOutputDto();

        $mapper = new Mapper();
        $result = $mapper->fromEntity($entity, $dto);
        
        self::assertInstanceOf(DummyOutputDto::class, $result);
        self::assertSame(42, $result->id);
        self::assertSame('John', $result->name);
    }

    public function testFromArrayMapsEachEntityAndClonesDto(): void
    {
        $entities = [
            new DummyEntity(1, 'A'),
            new DummyEntity(2, 'B'),
        ];
        $dto = new DummyOutputDto();

        $mapper = new Mapper();
        $results = $mapper->fromArray($entities, $dto);

        self::assertCount(2, $results);

        self::assertInstanceOf(DummyOutputDto::class, $results[0]);
        self::assertInstanceOf(DummyOutputDto::class, $results[1]);

        self::assertSame(1, $results[0]->id);
        self::assertSame('A', $results[0]->name);
        self::assertSame(2, $results[1]->id);
        self::assertSame('B', $results[1]->name);

        // Vérifie que ce sont bien des clones différents
        self::assertNotSame($results[0], $results[1]);
    }

    public function testFromEntityOverwritesExistingValuesOnDto(): void
    {
        $entity = new DummyEntity(10, 'X');
        $dto    = new DummyOutputDto();
        $dto->id   = 999;
        $dto->name = 'Y';

        $mapper = new Mapper();
        $result = $mapper->fromEntity($entity, $dto);

        self::assertSame(10, $result->id);
        self::assertSame('X', $result->name);
    }
}


final class DummyEntity
{
    public function __construct(
        private int $id,
        private string $name,
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }
}

final class DummyOutputDto implements \Rehark\ApiGeneratorBundle\Core\DTO\OutputDtoInterface
{
    public int $id;
    public string $name;
}
