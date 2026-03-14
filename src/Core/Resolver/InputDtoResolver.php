<?php

namespace Rehark\ApiGeneratorBundle\Core\Resolver;

use Rehark\ApiGeneratorBundle\Core\DTO\InputDtoInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class InputDtoResolver implements ValueResolverInterface {

    public function __construct(
        protected SerializerInterface $serializer,
        protected ValidatorInterface $validator,
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

        if ($argument->getType() && !is_a($argument->getType(), InputDtoInterface::class, true)) {
            return [];
        }
            

        if (in_array($request->getMethod(), ['GET', 'HEAD', 'OPTIONS'])) {
            return [];
        }

        $data = $request->getContent() ?: '{}';

        /** @var class-string $dtoClass */
        $dtoClass = $request->attributes->get('inputDtoClass');

        /** @var InputDtoInterface $inputDto */
        $inputDto = new $dtoClass();
        
        $this->serializer->deserialize($data, $dtoClass, 'json', [
            'object_to_populate' => $inputDto
        ]);

        $this->checkDto($inputDto);

        return [$inputDto];
    }

    private function checkDto(InputDtoInterface $inputDto): void {
        $violations = $this->validator->validate($inputDto);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()][] = $violation->getMessage();
            }
            throw new UnprocessableEntityHttpException();
        }
    }
}
