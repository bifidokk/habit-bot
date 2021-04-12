<?php

declare(strict_types=1);

namespace App\Service\Habit;

use App\Entity\Habit;
use App\Entity\User;
use App\Repository\HabitRepository;
use Elao\Enum\Enum;
use Symfony\Component\Workflow\StateMachine;

class HabitService
{
    private HabitRepository $habitRepository;
    private StateMachine $habitStateMachine;
    private StateMachine $habitCreationStateMachine;

    public function __construct(
        HabitRepository $habitRepository,
        StateMachine $habitStateMachine,
        StateMachine $habitCreationStateMachine
    ) {
        $this->habitRepository = $habitRepository;
        $this->habitStateMachine = $habitStateMachine;
        $this->habitCreationStateMachine = $habitCreationStateMachine;
    }

    public function createHabit(NewHabitDto $newHabit, User $user): Habit
    {
        $habit = new Habit();
        $habit->setDescription($newHabit->description);
        $habit->setUser($user);
        $habit->setCreationState(CreationHabitState::TITLE_ADDED);

        $this->habitRepository->save($habit);

        return $habit;
    }

    public function changeHabitCreationState(Habit $habit, Enum $state): void
    {
        $this->habitCreationStateMachine->apply($habit, (string) $state->getValue());
        $this->habitRepository->save($habit);
    }

    public function save(Habit $habit): void
    {
        $this->habitRepository->save($habit);
    }
}
