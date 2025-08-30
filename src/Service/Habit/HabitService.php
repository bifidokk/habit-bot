<?php

declare(strict_types=1);

namespace App\Service\Habit;

use App\Entity\Habit;
use App\Entity\User;
use App\Repository\HabitRepository;
use App\Service\Habit\Dto\CreateHabitRequest;
use App\Service\Habit\Dto\UpdateHabitRequest;
use App\Service\Habit\Exception\CouldNotGetHabit;
use Symfony\Contracts\Translation\TranslatorInterface;

class HabitService
{
    public function __construct(
        private readonly HabitRepository $habitRepository,
        private readonly RemindService $remindService,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function createDraftHabit(User $user): Habit
    {
        $habit = new Habit();
        $habit->setUser($user);

        $this->habitRepository->save($habit);

        return $habit;
    }

    public function createHabit(
        User $user,
        CreateHabitRequest $createHabitRequest,
    ): Habit {
        $this->removeUserDraftHabits($user);

        $habit = new Habit();
        $habit->setUser($user);
        $habit->setDescription($createHabitRequest->name);
        $habit->setRemindWeekDays($createHabitRequest->generateRemindWeekDaysInteger());
        $habit->setRemindAt(new \DateTimeImmutable($createHabitRequest->time));

        $this->publish($habit);

        return $habit;
    }

    public function removeUserDraftHabits(User $user): void
    {
        $this->habitRepository->removeUserHabitsWithState($user, HabitState::Draft);
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

    public function getHabitById(string $id): Habit
    {
        $habit = $this->habitRepository->findById($id);

        if ($habit === null) {
            throw new CouldNotGetHabit();
        }

        return $habit;
    }

    public function getUserHabits(User $user): array
    {
        return $this->habitRepository->findByUser($user);
    }

    public function getHabitPreviewText(Habit $habit): string
    {
        $habitRemind = $this->translator->trans('habit.preview.remind', [
            'days' => $this->remindService->getRemindDaysAsString($habit),
            'time' => $habit->getRemindAt() ? $habit->getRemindAt()->format('H:i') : '',
        ]);

        return sprintf(
            "*%s*\n%s",
            $this->markdownEscape($habit->getDescription()),
            $habitRemind
        );
    }

    public function getHabitRemoveConfirmText(Habit $habit): string
    {
        return sprintf(
            "%s\n%s",
            $this->translator->trans('habit.remove.confirm'),
            $this->getHabitPreviewText($habit),
        );
    }

    public function removeHabit(Habit $habit): void
    {
        $this->habitRepository->delete($habit);
    }

    public function updateHabit(Habit $habit, UpdateHabitRequest $updateHabitRequest): Habit
    {
        $habit->setDescription($updateHabitRequest->name);
        $habit->setRemindWeekDays($updateHabitRequest->generateRemindWeekDaysInteger());
        $habit->setRemindAt(new \DateTimeImmutable($updateHabitRequest->time));
        $habit->setNextRemindAt($this->remindService->getNextRemindTime(new \DateTimeImmutable(), $habit));

        $this->habitRepository->save($habit);

        return $habit;
    }

    public function updateHabitNextRemindTime(Habit $habit): void
    {
        $habit->setNextRemindAt($this->remindService->getNextRemindTime(new \DateTimeImmutable(), $habit));
        $this->habitRepository->save($habit);
    }

    public function save(Habit $habit): void
    {
        $this->habitRepository->save($habit);
    }

    private function markdownEscape(string $text): string
    {
        return str_replace([
            '\\', '-', '#', '*', '+', '`', '.', '[', ']', '(', ')', '!', '&', '<', '>', '_', '{', '}', ], [
                '\\\\', '\-', '\#', '\*', '\+', '\`', '\.', '\[', '\]', '\(', '\)', '\!', '\&', '\<', '\>', '\_', '\{', '\}',
            ], $text);
    }
}
