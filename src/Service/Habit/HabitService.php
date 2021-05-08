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
    private RemindService $remindService;

    public function __construct(
        HabitRepository $habitRepository,
        RemindService $remindService
    ) {
        $this->habitRepository = $habitRepository;
        $this->remindService = $remindService;
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
        $habit->setNextRemindAt($this->remindService->getNextRemindTime(new \DateTimeImmutable(), $habit));

        $this->habitRepository->save($habit);
    }

    public function getHabitByIdWithState(string $id, HabitState $state): Habit
    {
        $habit = $this->habitRepository->findByIdWithState($id, $state);

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
