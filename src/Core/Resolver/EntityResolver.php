<?php

namespace Rehark\ApiGeneratorBundle\Core\Resolver;

use Doctrine\ORM\EntityManagerInterface;
use Rehark\ApiGeneratorBundle\Core\Utils\EntityParam;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EntityResolver implements ValueResolverInterface {

    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function resolve(
        Request $request,
        ArgumentMetadata $argument
    ): iterable {

        if(empty($argument->getAttributes(EntityParam::class))) {
            return [];
        }

        $entities = $request->attributes->get('entities');
        $entity = null;

        foreach($entities as $key => $value) {

            if($value['param'] !== $argument->getName()) {
                continue;
            }

            $attribute = $request->attributes->get($key);
            $entity = $this->em
                ->getRepository($value['class'])
                ->findOneBy([
                    $value['property'] => $attribute
                ])
            ;
        }

        if(!$entity) {
            throw new NotFoundHttpException();
        }

        yield $entity;
    }
}