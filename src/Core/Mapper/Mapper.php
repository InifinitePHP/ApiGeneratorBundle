<?php

namespace Rehark\ApiGeneratorBundle\Core\Mapper;

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

        foreach (get_object_vars($output) as $key => $value) {
            $entityMethod = 'get' . ucfirst($key);
            $output->$key = $entity->$entityMethod();
        }

        return $output;
    }
}
