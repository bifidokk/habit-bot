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

    public function addHabit(string $habitDescription, User $user): void
    {
        $habit = new Habit();
        $habit->setDescription($habitDescription);
        $habit->setUser($user);

        $this->habitRepository->save($habit);
    }
}
