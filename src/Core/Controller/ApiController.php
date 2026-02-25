<?php

namespace Rehark\ApiGeneratorBundle\Core\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Rehark\ApiGeneratorBundle\Core\DTO\InputDtoInterface;
use Rehark\ApiGeneratorBundle\Core\DTO\OutputDtoInterface;
use Rehark\ApiGeneratorBundle\Core\DTO\SearchDtoInterface;
use Rehark\ApiGeneratorBundle\Core\Mapper\Mapper;
use Rehark\ApiGeneratorBundle\Core\State\EntityBuilder;
use Rehark\ApiGeneratorBundle\Core\Utils\EntityParam;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
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
        protected ValidatorInterface $validator,
        protected Mapper $mapper,
    ) {}

    public function search(
        SearchDtoInterface $input,
        OutputDtoInterface $outputClass
    ) : JsonResponse {

        /** @var class-string $class */
        $class = $this->getEntityClass();

        $index = $input->getIndex();
        $limit = $input->getLimit();

        $startAt = ($index - 1) * $limit;
        
        $qb = $this->em->createQueryBuilder()
            ->select('e')
            ->from($class, 'e')
            ->setMaxResults($limit)
            ->setFirstResult($startAt)
        ;

        /** @var array<int, object> $entities */
        $entities = $qb->getQuery()->getResult();
        
        return new JsonResponse([
            'data' => $this->mapper->fromArray($entities, $outputClass)
        ]);
    }

    public function show(
        #[EntityParam] object $entity,
        InputDtoInterface $input,
        OutputDtoInterface $outputClass
    ) : JsonResponse {        
        return new JsonResponse([
            'data' => $this->mapper->fromEntity($entity, $outputClass)
        ]);
    }

    public function create(
        InputDtoInterface $input,
        OutputDtoInterface $outputClass
    ) : JsonResponse {

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
            'data' => $this->mapper->fromEntity($entity, $outputClass)
        ]);
    }

    public function update(
        #[EntityParam] object $entity,
        InputDtoInterface $input,
        OutputDtoInterface $outputClass
    ) : JsonResponse {
        return new JsonResponse(1);
    }

    Public function delete(
        #[EntityParam] object $entity,
        InputDtoInterface $input,
        OutputDtoInterface $outputClass
    ) : JsonResponse {

        $this->em->remove($entity);
        $this->em->flush();

        return new JsonResponse([
            'data' => $this->mapper->fromEntity($entity, $outputClass)
        ]);
    }
}
