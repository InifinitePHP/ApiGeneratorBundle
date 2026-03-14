<?php

namespace Rehark\ApiGeneratorBundle\Core\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Rehark\ApiGeneratorBundle\Core\DTO\InputDtoInterface;
use Rehark\ApiGeneratorBundle\Core\DTO\OutputDtoInterface;
use Rehark\ApiGeneratorBundle\Core\DTO\SearchDtoInterface;
use Rehark\ApiGeneratorBundle\Core\Mapper\Mapper;
use Rehark\ApiGeneratorBundle\Core\State\SmartEntityBuilder;
use Rehark\ApiGeneratorBundle\Core\Utils\EntityParam;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

abstract class ApiController extends AbstractController implements ApiControllerInterface
{  

    protected int $remainingDepth = 2;
    protected int $maxArraySize = 10;
    protected string $voter;

    public static abstract function actions(): array;
    protected abstract function getEntityClass(): string;
    protected abstract function getVoter(): string;
    protected abstract function getPermissionQuery(): string;

    public function __construct(
        protected EntityManagerInterface $em,
        protected RequestStack $request,
        protected Mapper $mapper,
    ) {}

    public function search(
        SearchDtoInterface $input,
        OutputDtoInterface $outputClass
    ) : JsonResponse {

        $this->denyAccessUnlessGranted(
            $this->getVoter()::LIST
        );

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

        // apply permission query !!!
        $permissionQueryClass = $this->getPermissionQuery();        
        $permissionQuery = new $permissionQueryClass();
        $permissionQuery->apply($qb, $this->getUser());

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
        
        $this->denyAccessUnlessGranted(
            $this->getVoter()::READ,
            $entity
        );

        return new JsonResponse([
            'data' => $this->mapper->fromEntity($entity, $outputClass)
        ]);
    }

    public function create(
        InputDtoInterface $input,
        OutputDtoInterface $outputClass
    ) : JsonResponse {

        $this->denyAccessUnlessGranted(
            $this->getVoter()::CREATE
        );

        $entity = (new SmartEntityBuilder($this->em))->build(
            $this->getEntityClass(),
            $input,
        );

        $this->em->persist($entity);
        $this->em->flush();

        return new JsonResponse([
            'data' => $this->mapper->fromEntity($entity, $outputClass)
        ]);
    }

    public function update(
        #[EntityParam] object $entity,
        InputDtoInterface $input,
        OutputDtoInterface $outputClass
    ) : JsonResponse {

        $this->denyAccessUnlessGranted(
            $this->getVoter()::UPDATE,
            $entity
        );
                
        $entity = (new SmartEntityBuilder($this->em))->build(
            $this->getEntityClass(),
            $input,
            $entity
        );

        $this->em->persist($entity);
        $this->em->flush();

        return new JsonResponse([
            'data' => $this->mapper->fromEntity($entity, $outputClass)
        ]);
    }

    Public function delete(
        #[EntityParam] object $entity,
        InputDtoInterface $input,
        OutputDtoInterface $outputClass
    ) : JsonResponse {

        $this->denyAccessUnlessGranted(
            $this->getVoter()::DELETE,
            $entity
        );

        $this->em->remove($entity);
        $this->em->flush();

        return new JsonResponse([
            'data' => $this->mapper->fromEntity($entity, $outputClass)
        ]);
    }
}
