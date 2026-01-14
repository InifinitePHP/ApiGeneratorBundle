<?php

namespace Rehark\ApiGeneratorBundle\Core\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;


abstract class ApiController extends AbstractController implements ApiControllerInterface
{  

    public static abstract function actions(): array;
    protected abstract function getEntityClass(): string;

    public function __construct(
        protected EntityManagerInterface $em,
        protected RequestStack $request
    ) {}

    protected function getRequestBody(): mixed {
        return json_decode($this->request->getCurrentRequest()->getContent());
    }

    public function list() : JsonResponse {
        return new JsonResponse([1, 1]);
    }

    public function show() : JsonResponse {
        return new JsonResponse(1);
    }

    public function create() : JsonResponse {
        return new JsonResponse(1);
    }

    public function update() : JsonResponse {
        return new JsonResponse(1);
    }

    Public function delete() : JsonResponse {
        return new JsonResponse(1);
    }
}
