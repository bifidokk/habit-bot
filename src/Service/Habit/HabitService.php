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

    public function createHabit(HabitDescriptionDto $habitDescription, User $user): Habit
    {
        $habit = new Habit();
        $habit->setDescription($habitDescription->description);
        $habit->setUser($user);

        $this->habitRepository->save($habit);

        return $habit;
    }

    public function changeHabitCreationState(Habit $habit, Enum $state): void
    {
        $this->habitCreationStateMachine->apply($habit, (string) $state->getValue());
        $this->habitRepository->save($habit);
    }

    public function changeHabitState(Habit $habit, Enum $state): void
    {
        $this->habitStateMachine->apply($habit, (string) $state->getValue());
        $this->habitRepository->save($habit);
    }

    public function removeUserDraftHabits(User $user): void
    {
        $this->habitRepository->removeUserHabitsWithState($user, HabitState::get(HabitState::DRAFT));
    }

    public function save(Habit $habit): void
    {
        $this->habitRepository->save($habit);
    }
}
