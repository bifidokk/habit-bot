<?php

declare(strict_types=1);

namespace App\Service\Habit;

use App\Entity\Habit;
use App\Entity\User;
use App\Repository\HabitRepository;
use App\Service\Habit\Exception\CouldNotGetHabit;

class HabitService
{
    private HabitRepository $habitRepository;

    public function __construct(
        HabitRepository $habitRepository
    ) {
        $this->habitRepository = $habitRepository;
    }

    public function createHabit(User $user): Habit
    {
        $habit = new Habit();
        $habit->setUser($user);

        $this->habitRepository->save($habit);

        return $habit;
    }

    public function removeUserDraftHabits(User $user): void
    {
        $this->habitRepository->removeUserHabitsWithState($user, HabitState::get(HabitState::DRAFT));
    }

    public function publish(Habit $habit): void
    {
        $habit->publish();
        $this->habitRepository->save($habit);
    }

    public function getHabit(string $id): Habit
    {
        $habit = $this->habitRepository->find($id);

        if ($habit === null) {
            throw new CouldNotGetHabit();
        }

        return $habit;
    }

    public function save(Habit $habit): void
    {
        $this->habitRepository->save($habit);
    }
}
