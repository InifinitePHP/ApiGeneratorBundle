<?php

namespace Rehark\ApiGeneratorBundle\Core\Resolver;

use Rehark\ApiGeneratorBundle\Core\DTO\DefaultInputDto;
use Rehark\ApiGeneratorBundle\Core\DTO\InputDtoInterface;
use Rehark\ApiGeneratorBundle\Core\DTO\SearchDtoInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Serializer\SerializerInterface;

class InputDtoResolver implements ValueResolverInterface {

    public function __construct(
        protected SerializerInterface $serializer,
    ) {}

    /**
     * @param Request $request
     * @param ArgumentMetadata $argument
     * 
     * @return array<int, InputDtoInterface>
     */
    public function resolve(
        Request $request,
        ArgumentMetadata $argument
    ): iterable {

        if (
            $argument->getType() !== InputDtoInterface::class
            && $argument->getType() !== SearchDtoInterface::class
        ) {
            return [];
        }
            

        if (in_array($request->getMethod(), ['GET', 'HEAD', 'OPTIONS'])) {
            return [];
        }

        $data = $request->getContent() ?: '{}';

        /** @var class-string $dtoClass */
        $dtoClass = $request->attributes->get('inputDtoClass');

        if(!$dtoClass) {
            
            $defaultDto = new DefaultInputDto();
            $data = (object) json_decode($data);

            foreach (get_object_vars($data) as $key => $value) {
                $defaultDto->$key = $value;
            }
            return [$defaultDto];
        }

        /** @var InputDtoInterface $inputDto */
        $inputDto = new $dtoClass();
        
        $this->serializer->deserialize($data, $dtoClass, 'json', [
            'object_to_populate' => $inputDto
        ]);

        return [$inputDto];
    }
}
