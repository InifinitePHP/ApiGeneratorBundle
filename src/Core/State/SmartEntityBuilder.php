<?php

namespace Rehark\ApiGeneratorBundle\Core\State;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Exception;
use Rehark\ApiGeneratorBundle\Core\Exception\EntityBuildingException;
use Rehark\ApiGeneratorBundle\Core\Exception\MethodException;
use Rehark\ApiGeneratorBundle\Core\Type\FieldTypeEnum;

class SmartEntityBuilder {

    public function __construct(
        private EntityManagerInterface $em
    ) {}

    /**
     * @param string $class
     * @param object $data
     * @param array<int, string> $trace
     */
    public function build(
        string $class,
        object $data,
        ?object $entity = null,
        ?array $trace = []
    ): object {

        $entity ??= new $class();
        $metadata = $this->em->getClassMetadata($class);

        foreach((array) $data as $field => $value) {

            $temp_trace = $trace;
            array_push($temp_trace, $field);
            
            if($metadata->hasField($field)) {
                $castedValue = $this->handleField($metadata, $field, $value, $trace);
                $setter = $this->getSetter($entity, $field);
                $entity->$setter($castedValue);
                continue;
            }

            if($metadata->hasAssociation($field)) {
                $this->handleAssociation($metadata, $field, $value, $trace);
                continue;
            }

            $this->handleEmbed($metadata, $field, $value, $trace);
        }

        return $entity;
    }

    /**
     * @param ClassMetadata<object> $metadata
     * @param array<int, string> $trace
     */
    private function handleField(
        ClassMetadata $metadata,
        string $field,
        mixed $value,
        array $trace
    ): mixed {

        if($metadata->isNullable($field) && $value === null) { 
            return null;
        }

        if($value === null) {
            throw new EntityBuildingException('Unexpected null for ' . $field, $trace);
        }

        $doctrineType = $metadata->getTypeOfField($field);

        if ($doctrineType === null) {
            throw new EntityBuildingException('Unexpected field type for ' . $field, $trace);
        }

        $type = FieldTypeEnum::fromDoctrineType($metadata->getTypeOfField($field));
        return $type->cast($value);
    }

    /**
     * @param ClassMetadata<object> $metadata
     * @param array<int, string> $trace
     */
    private function handleAssociation(
        ClassMetadata $metadata,
        string $field,
        mixed $value,
        array $trace
    ): void {
        throw new Exception('Not implemented yet !');
    }

    /**
     * @param ClassMetadata<object> $metadata
     * @param array<int, string> $trace
     */
    private function handleEmbed(
        ClassMetadata $metadata,
        string $field,
        mixed $value,
        array $trace
    ): void {
        throw new Exception('Not implemented yet !');
    }

    private function getSetter(
        object $entity,
        string $field,
    ): string {

        $method = 'set' . ucfirst($field);

        if(!method_exists($entity, $method)) {
            throw new MethodException('Method {method} not exists in class {class}.', $entity, $method);
        }

        return $method;
    }

}