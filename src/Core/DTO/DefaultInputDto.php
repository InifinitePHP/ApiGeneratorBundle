<?php

namespace Rehark\ApiGeneratorBundle\Core\DTO;

use AllowDynamicProperties;

#[AllowDynamicProperties]
class DefaultInputDto implements InputDtoInterface {

    public function __set(string $name, mixed $value): void 
    {
        $this->$name = $value;
    }

    public function __get(string $name): mixed 
    {
        return $this->$name;
    }

}