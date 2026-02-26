<?php

namespace Rehark\ApiGeneratorBundle\Core\DTO;

interface SearchDtoInterface extends InputDtoInterface {
    public function getIndex(): int;
    public function getLimit(): int;
}