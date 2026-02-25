<?php

namespace App\Api\DTO{{NAMESPACE}};

use Rehark\ApiGeneratorBundle\Core\DTO\SearchDtoInterface;

/**
 * Search DTO for {{NAME}} entity
 * Used to validate and map input data from JSON
 */
class Search{{NAME}}Input implements SearchDtoInterface
{
    // Example: public ?string $name = null;
    // Add properties that match your entity fields

    public ?string $index = null;
    public ?string $limit = null;

    public function getIndex(): int {
        return $this->index ?? 1;
    }

    public function getLimit(): int {
        return $this->limit ?? 20;
    }
}
