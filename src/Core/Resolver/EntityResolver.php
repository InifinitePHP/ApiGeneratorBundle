<?php

namespace Rehark\ApiGeneratorBundle\Core\Resolver;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Rehark\ApiGeneratorBundle\Core\Utils\EntityParam;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EntityResolver implements ValueResolverInterface {

    public function __construct(
        private EntityManagerInterface $em
    ) {}

    /**
     * @param Request $request
     * @param ArgumentMetadata $argument
     * 
     * @return array<int, object>
     */
    public function resolve(
        Request $request,
        ArgumentMetadata $argument
    ): iterable {

        if(empty($argument->getAttributes(EntityParam::class))) {
            return [];
        }

        /** @var array<string, array<string, string>> $entities */
        $entities = $request->attributes->get('entities');
        $entity = null;

        foreach($entities as $key => $value) {

            if($value['param'] !== $argument->getName()) {
                continue;
            }

            $attribute = $request->attributes->get($key);

            /** @var class-string $class */
            $class = $value['class'];
            
            /** @var EntityRepository<object> $repo */
            $repo = $this->em->getRepository($class);

            /** @var object|null $entity */
            $entity = $repo->findOneBy([
                $value['property'] => $attribute
            ]);
        }

        if(!$entity) {
            throw new NotFoundHttpException();
        }

        return [$entity];
    }
}