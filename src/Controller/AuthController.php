<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\Telegram\TelegramAuthService;
use App\Service\Telegram\TelegramUser;
use App\Service\User\UserService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class AuthController extends AbstractController
{
    public function __construct(
        private readonly TelegramAuthService $tgAuth,
        private readonly UserRepository $userRepository,
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly UserService $userService,
    ) {
    }

    #[Route('/api/auth/telegram', methods: ['POST'])]
    public function telegramLogin(Request $request): JsonResponse
    {
        $initData = $request->toArray()['initData'] ?? '';
        $tgUser = $this->tgAuth->verify($initData);

        if (!$tgUser instanceof TelegramUser) {
            return $this->json([
                'error' => 'Invalid Telegram auth',
            ], 403);
        }

        $user = $this->userRepository->findOneByTelegramId((int) $tgUser->getId());

        if (!$user) {
            $user = $this->userService->createFromTelegramUser($tgUser);
        }

        return $this->json([
            'token' => $this->jwtManager->create($user),
            'user' => [
                'id' => $user->getTelegramId(),
                'username' => $user->getUsername(),
            ],
        ]);
    }
}
