<?php

namespace Rehark\ApiGeneratorBundle\Core\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Rehark\ApiGeneratorBundle\Core\Mapper\Mapper;
use Rehark\ApiGeneratorBundle\Core\State\EntityBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class ApiController extends AbstractController implements ApiControllerInterface
{  

    protected int $remainingDepth = 2;
    protected int $maxArraySize = 10;

    public static abstract function actions(): array;
    protected abstract function getEntityClass(): string;

    public function __construct(
        protected EntityManagerInterface $em,
        protected RequestStack $request,
        protected SerializerInterface $serializer,
        protected ValidatorInterface $validator,
        protected Mapper $mapper,
    ) {}

    protected function buildDto(
        string $inputDtoClass
    ): object {

        $input = $this->request->getCurrentRequest()?->getContent() ?? '{}';
        /** @var object $inputDto */
        $inputDto = new $inputDtoClass();
        
        // Appel sans assigner le retour (in-place population)
        $this->serializer->deserialize($input, $inputDto::class, 'json', [
            'object_to_populate' => $inputDto
        ]);

        $violations = $this->validator->validate($inputDto);
        if (count($violations) > 0) {
            throw new UnprocessableEntityHttpException();
        }

        if (!($inputDto instanceof $inputDtoClass)) {
            throw new \RuntimeException('DTO creation failed');
        }

        return $inputDto;
    }

    public function list(
        string $inputDtoClass,
        string $outputDtoClass
    ) : JsonResponse {

        $input = $this->buildDto($inputDtoClass);

        $class = $this->getEntityClass();

        /** @phpstan-ignore-next-line */
        $repo = $this->em->getRepository($class);
        $entities = $repo->findAll();

        return new JsonResponse([
            'data' => $this->mapper->fromArray($entities, $outputDtoClass)
        ]);
    }

    public function show() : JsonResponse {
        return new JsonResponse(1);
    }

    public function create(
        string $inputDtoClass,
        string $outputDtoClass
    ) : JsonResponse {

        $input = $this->buildDto($inputDtoClass);

        $entity = (new EntityBuilder())->build(
            $this->getEntityClass(),
            $input,
            $this->remainingDepth,
            $this->maxArraySize,
        );

        try {
            $this->em->persist($entity);
            // $this->em->flush();
        } catch (Exception $e) {
            throw new Exception(
                "persitante exception not implemented yet ! \n"
                . $e->getMessage()
            );
        }

        return new JsonResponse([
            'data' => $this->mapper->fromEntity($entity, $outputDtoClass)
        ]);
    }

    public function update() : JsonResponse {
        return new JsonResponse(1);
    }

    Public function delete() : JsonResponse {
        return new JsonResponse(1);
    }
}
