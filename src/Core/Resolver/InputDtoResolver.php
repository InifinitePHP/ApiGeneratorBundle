<?php

namespace Rehark\ApiGeneratorBundle\Core\Resolver;

use Rehark\ApiGeneratorBundle\Core\DTO\DefaultInputDto;
use Rehark\ApiGeneratorBundle\Core\DTO\InputDtoInterface;
use stdClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Serializer\SerializerInterface;

class InputDtoResolver implements ValueResolverInterface {

    public function __construct(
        protected SerializerInterface $serializer,
    ) {}

    public function resolve(
        Request $request,
        ArgumentMetadata $argument
    ): iterable {

        if ($argument->getType() !== InputDtoInterface::class) {
            return [];
        }
        
        if (in_array($request->getMethod(), ['GET', 'HEAD', 'OPTIONS'])) {
            return [];
        }

        $data = $request->getContent() ?: '{}';
        $dtoClass = $request->attributes->get('inputDtoClass');

        if(!$dtoClass) {
            $defaultDto = new DefaultInputDto();
            foreach (json_decode($data) as $key => $value) {
                $defaultDto->$key = $value;
            }
            return [$defaultDto];
        }

        
        $inputDto = new $dtoClass();
        
        $this->serializer->deserialize($data, $dtoClass, 'json', [
            'object_to_populate' => $inputDto
        ]);

        yield $inputDto;
    }
}
