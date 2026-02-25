<?php

namespace Rehark\ApiGeneratorBundle\Core\DTO;

interface SearchDtoInterface {
    public function getIndex(): int;
    public function getLimit(): int;
}