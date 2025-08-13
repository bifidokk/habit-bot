<?php
declare(strict_types=1);

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class TestController extends AbstractController
{
    #[Route('/api/test', name: 'api_test', methods: ['GET'])]
    public function index(#[CurrentUser] $user): JsonResponse
    {
        return $this->json([
            'message' => 'You are authenticated!',
            'user' => $user ? $user->getUserIdentifier() : null,
            'roles' => $user ? $user->getRoles() : [],
        ]);
    }
}
