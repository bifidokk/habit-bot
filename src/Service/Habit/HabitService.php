<?php

declare(strict_types=1);

namespace App\Service\Habit;

use App\Entity\Habit;
use App\Entity\User;
use App\Repository\HabitRepository;
use App\Service\Habit\Exception\CouldNotGetHabit;
use Symfony\Contracts\Translation\TranslatorInterface;

class HabitService
{
    public function __construct(
        private HabitRepository $habitRepository,
        private RemindService $remindService,
        private TranslatorInterface $translator,
    ) {}

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
