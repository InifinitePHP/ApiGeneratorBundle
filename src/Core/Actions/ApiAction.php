<?php

namespace Rehark\ApiGeneratorBundle\Core\Actions;

class ApiAction
{

    /**
     * @param string $name
     * @param array<int, string> $methods
     * @param string $path
     */
    public function __construct(
        public string $name,
        public array $methods = ['GET'],
        public string $path = '',
        public ?string $inputDto = null,
        public ?string $outputDto = null,
        public array $paramConverters = []
    ) {}
}
