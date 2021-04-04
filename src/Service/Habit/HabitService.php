<?php

declare(strict_types=1);

namespace App\Service\Habit;

use App\Entity\Habit;
use App\Entity\User;
use App\Repository\HabitRepository;

class HabitService
{
    private HabitRepository $habitRepository;

    public function __construct(HabitRepository $habitRepository)
    {
        $this->habitRepository = $habitRepository;
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
}
