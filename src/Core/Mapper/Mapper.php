<?php

namespace Rehark\ApiGeneratorBundle\Core\Mapper;

use ReflectionClass;
use ReflectionProperty;
use Rehark\ApiGeneratorBundle\Core\DTO\OutputDtoInterface;

class Mapper
{

    /**
     * @param array<int, object> $entities
     * @param OutputDtoInterface $output
     * 
     * @return array<int, object>
     */
    public function fromArray(
        array $entities,
        OutputDtoInterface $output
    ): array {

        return array_map(
            function ($entity) use ($output) {
                return $this->fromEntity($entity, clone $output);
            },
            $entities
        );
    }

    /**
     * @param object $entity
     * @param OutputDtoInterface $output
     * 
     * @return object
     */
    public function fromEntity(
        object $entity,
        OutputDtoInterface $output
    ): object {

        $reflection = new ReflectionClass($output);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

        foreach ($properties as $value) {
            $key = $value->name;
            $entityMethod = 'get' . ucfirst($key);
            $output->$key = $entity->$entityMethod();
        }

        return $output;
    }
}
