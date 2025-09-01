<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Habit;
use App\Entity\User;
use App\Service\Habit\Factory\HabitResponseDtoFactory;
use App\Service\Habit\HabitCompletion\HabitCompleteRequest;
use App\Service\Habit\HabitCompletion\HabitCompletionService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class HabitCompletionController extends AbstractController
{
    public function __construct(
        private readonly HabitCompletionService $habitCompletionService,
        private readonly HabitResponseDtoFactory $habitResponseDtoFactory,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route('/api/habits/{id}/completions', name: 'api_create_habit_completion', methods: ['POST'])]
    public function createCompletion(
        #[CurrentUser]
        ?User $user,
        ?Habit $habit,
        #[MapRequestPayload]
        HabitCompleteRequest $habitCompleteRequest,
    ): JsonResponse {
        if (! $user instanceof User) {
            return $this->json([], Response::HTTP_UNAUTHORIZED);
        }

        if (! $habit) {
            return $this->json([], Response::HTTP_NOT_FOUND);
        }

        if ($habit->getUser()?->getId() !== $user->getId()) {
            return $this->json([], Response::HTTP_FORBIDDEN);
        }

        try {
            $this->habitCompletionService->createHabitCompletion(
                $habit,
                $habitCompleteRequest
            );
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());
        }

        return $this->json(
            $this->habitResponseDtoFactory->createFromEntity($habit),
        );
    }
}
