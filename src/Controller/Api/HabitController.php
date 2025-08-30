<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Habit;
use App\Entity\User;
use App\Repository\HabitRepository;
use App\Service\Habit\Dto\CreateHabitRequest;
use App\Service\Habit\Factory\HabitResponseDtoFactory;
use App\Service\Habit\HabitService;
use App\Service\Habit\HabitState;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class HabitController extends AbstractController
{
    public function __construct(
        private readonly HabitRepository $habitRepository,
        private readonly HabitResponseDtoFactory $habitResponseDtoFactory,
        private readonly HabitService $habitService,
    ) {
    }

    #[Route('/api/habits', name: 'api_habits', methods: ['GET'])]
    public function index(#[CurrentUser] ?User $user): JsonResponse
    {
        if (! $user instanceof User) {
            return $this->json([], Response::HTTP_UNAUTHORIZED);
        }

        /** @var Habit[] $habits */
        $habits = $this->habitRepository->findBy([
            'user' => $user,
            'state' => HabitState::Published,
        ]);

        return $this->json(
            $this->habitResponseDtoFactory->createFromEntities($habits),
        );
    }

    #[Route('/api/habits', name: 'api_create_habit', methods: ['POST'])]
    public function create(
        #[CurrentUser] ?User $user,
        #[MapRequestPayload] CreateHabitRequest $createHabitRequest,
    ): JsonResponse {
        if (! $user instanceof User) {
            return $this->json([], Response::HTTP_UNAUTHORIZED);
        }

        $habit = $this->habitService->createHabit($user, $createHabitRequest);

        return $this->json(
            $this->habitResponseDtoFactory->createFromEntity($habit),
        );
    }
}
