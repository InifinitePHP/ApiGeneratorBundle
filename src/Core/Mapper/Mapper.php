<?php

namespace Rehark\ApiGeneratorBundle\Core\Mapper;

class Mapper
{

    /**
     * @param array<int, object> $entities
     * @param ?string $class
     * 
     * @return array<int, object>
     */
    public function fromArray(
        array $entities,
        ?string $class
    ): array {

        if(!$class) {
            return $entities;
        }

        return array_map(
            function ($entity) use ($class) {
                return $this->fromEntity($entity, $class);
            },
            $entities
        );
    }

    /**
     * @param object $entity
     * @param ?string $class
     * 
     * @return object
     */
    public function fromEntity(
        object $entity,
        ?string $class
    ): object {

        if(!$class) {
            return $entity;
        }

        $output = new $class();

        foreach (get_object_vars($output) as $key => $value) {
            $entityMethod = 'get' . ucfirst($key);
            $output->$key = $entity->$entityMethod();
        }

        return $output;
    }
}
