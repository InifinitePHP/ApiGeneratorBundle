<?php

namespace Rehark\ApiGeneratorBundle\Core\State;

use Rehark\ApiGeneratorBundle\Core\Exception\EntityBuildingException;
use Rehark\ApiGeneratorBundle\Core\Exception\MethodException;

/** @deprecated */
class EntityBuilder {

    /**
     * @param string $class
     * @param object $data
     * @param int $remainingDepth
     * @param int $maxArraySize
     * @param array<int, string> $trace
     */
    public function build(
        string $class,
        object $data,
        int $remainingDepth = 2,
        int $maxArraySize = 10,
        array $trace = []
    ): Object {

        if ($remainingDepth < 0) {
            throw new EntityBuildingException('Maximum depth exceeded', $trace);
        }

        $entity = new $class();

        foreach((array) $data as $key => $value) {

            $temp_trace = $trace;
            array_push($temp_trace, $key);

            if(is_null($value) || is_scalar($value)) {
                $this->setScalar($entity, $key, $value);
                continue;
            }

            if(is_object($value)) {
                $this->setObject($entity, $key, $value, $remainingDepth, $maxArraySize, $temp_trace);
                continue;
            }

            if(is_array($value)) {
                $this->setArray($entity, $key, array_values($value), $remainingDepth, $maxArraySize, $temp_trace);
                continue;
            }

            throw new EntityBuildingException('Unexpected type for ' . $key, $trace);
        }

        return $entity;
    }

    private function getSetter(
        object $entity,
        string $key,
    ): string {

        $method = 'set' . ucfirst($key);

        if(!method_exists($entity, $method)) {
            throw new MethodException('Method {method} not exists in class {class}.', $entity, $method);
        }

        return $method;
    }

    private function getAdder(
        object $entity,
        string $key,
    ): string {

        $baseName = (str_ends_with($key, 's')) ? substr($key, 0, -1) : $key;
        $method = 'add' . ucfirst($baseName);

        if(!method_exists($entity, $method)) {
            throw new MethodException('Method {method} not exists in class {class}.', $entity, $method);
        }

        return $method;
    }

    private function getMethodType(
        object $entity,
        string $method,
    ): string {

        $reflection = new \ReflectionMethod($entity, $method);
        $params = $reflection->getParameters();
    
        if (count($params) !== 1) {
            throw new MethodException("{class}::{method} class must have exactly one parameter", $entity, $method);
        }
    
        $type = $params[0]->getType();
    
        if (!$type) {
            throw new MethodException("Cannot determine type for parameter in {class}::{method}" , $entity, $method);
        }

        if (!($type instanceof \ReflectionNamedType)) {
            throw new MethodException("Unsupported multiple type for parameter in {class}::{method}", $entity, $method);
        }
    
        return $type->getName();
    }

    private function setScalar(
        object $entity,
        string $key,
        int|float|string|bool|null $value
    ): void {
        $method = $this->getSetter($entity, $key);
        $entity->$method($value);
    }

    /**
     * @param object $entity
     * @param string $key
     * @param object $data
     * @param int $remainingDepth
     * @param int $maxArraySize
     * @param array<int, string> $trace
     */
    private function setObject(
        object $entity,
        string $key,
        object $data,
        int $remainingDepth,
        int $maxArraySize,
        array $trace = []
    ): void {
        $method = $this->getSetter($entity, $key);
        $propertyType = $this->getMethodType($entity, $method);
        $nestedValue = $this->build($propertyType, $data, $remainingDepth - 1, $maxArraySize, $trace);
        $entity->$method($nestedValue);
    }

    /**
     * @param object $entity
     * @param string $key
     * @param array<int, mixed> $data
     * @param int $remainingDepth
     * @param int $maxArraySize
     * @param array<int, string> $trace
     */
    private function setArray(
        object $entity,
        string $key,
        array $data,
        int $remainingDepth,
        int $maxArraySize,
        array $trace = []
    ): void {

        if(sizeof($data) > $maxArraySize) {
            throw new EntityBuildingException('Array size to big !', $trace);
        }

        $method = $this->getAdder($entity, $key);
        $propertyType = $this->getMethodType($entity, $method);
        
        foreach($data as $value) {

            $temp_trace = $trace;
            array_push($temp_trace, [$key]);

            $nestedValue = $value;

            if($propertyType == 'array') {
                throw new EntityBuildingException('Array of Array is not supported.', $trace);
            }
            
            if(!in_array($propertyType, ['int', 'float', 'string', 'bool', 'null', 'array'])) {
                $nestedValue = $this->build($propertyType, (object) $value, $remainingDepth - 1, $maxArraySize, $trace);
            }
            
            $entity->$method($nestedValue);
        }
        
    } 

}