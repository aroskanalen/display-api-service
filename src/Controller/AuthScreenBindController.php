<?php

namespace App\Controller;

use App\Repository\ScreenRepository;
use App\Security\ScreenAuthenticator;
use App\Utils\ValidationUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class AuthScreenBindController extends AbstractController
{
    public function __construct(
        private ScreenAuthenticator $authScreenService,
        private ValidationUtils $validationUtils,
        private ScreenRepository $screenRepository
    ) {}

    public function __invoke(Request $request, string $id): Response
    {
        $screenUlid = $this->validationUtils->validateUlid($id);
        $screen = $this->screenRepository->find($screenUlid);

        $body = $request->toArray();
        $bindKey = $body['bindKey'];

        if (!isset($bindKey)) {
            throw new \HttpException('Missing key', Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->authScreenService->bindScreen($screen, $bindKey);
        } catch (\Exception $exception) {
            return new JsonResponse('Key not accepted', Response::HTTP_BAD_REQUEST);
        }

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
