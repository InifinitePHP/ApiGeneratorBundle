<?php

namespace Rehark\ApiGeneratorBundle\Core\Actions;

class ApiAction
{

    /**
     * @param string $name
     * @param array<int, string> $methods
     * @param string $path
     * @param class-string $inputDto
     * @param class-string $outputDto
     * @param array<string, array<string, string>> $paramConverters 
     */
    public function __construct(
        public string $name,
        public array $methods = ['GET'],
        public string $path = '',
        public string $inputDto,
        public string $outputDto,
        public array $paramConverters = []
    ) {}
}
