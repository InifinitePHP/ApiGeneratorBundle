<?php

namespace Rehark\ApiGeneratorBundle\Core\Resolver;

use Rehark\ApiGeneratorBundle\Core\DTO\DefaultOutputDto;
use Rehark\ApiGeneratorBundle\Core\DTO\OutputDtoInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Serializer\SerializerInterface;

class OutputDtoResolver implements ValueResolverInterface {

    public function __construct(
        protected SerializerInterface $serializer,
    ) {}

    /**
     * @param Request $request
     * @param ArgumentMetadata $argument
     * 
     * @return array<int, OutputDtoInterface>
     */
    public function resolve(
        Request $request,
        ArgumentMetadata $argument
    ): iterable {

        if ($argument->getType() !== OutputDtoInterface::class) {
            return [];
        }

        /** @var class-string $outputDtoClass */
        $outputDtoClass = $request->attributes->get('outputDtoClass') ?? new DefaultOutputDto();
        
        /** @var OutputDtoInterface $outputDto */
        $outputDto = new $outputDtoClass();
        
        return [$outputDto];
    }
}
