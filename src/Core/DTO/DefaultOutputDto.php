<?php

namespace Rehark\ApiGeneratorBundle\Core\DTO;

use AllowDynamicProperties;

#[AllowDynamicProperties]
class DefaultOutputDto implements OutputDtoInterface {

    public function __set(string $name, mixed $value): void 
    {
        $this->$name = $value;
    }

    public function __get(string $name): mixed 
    {
        return $this->$name;
    }

}