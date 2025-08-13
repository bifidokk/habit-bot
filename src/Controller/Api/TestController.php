<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class TestController extends AbstractController
{
    #[Route('/api/test', name: 'api_test', methods: ['GET'])]
    public function index(#[CurrentUser] ?User $user): JsonResponse
    {
        return $this->json([
            'message' => 'You are authenticated!',
            'user' => $user?->getUserIdentifier(),
            'roles' => $user ? $user->getRoles() : [],
        ]);
    }
}
