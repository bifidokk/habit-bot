<?php

declare(strict_types=1);

namespace App\Entity;

use App\Service\Habit\CreationHabitState;
use App\Service\Habit\HabitState;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidV4Generator;

/**
 * @ORM\Entity(repositoryClass="App\Repository\HabitRepository")
 */
class Habit
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidV4Generator::class)
     */
    private string $id = '';

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="habits")
     */
    private ?User $user = null;

    /**
     * @ORM\Column(type="string")
     */
    private string $description = '';

    /**
     * @ORM\Column(type="habit_state", length=32, options={"default"="draft"})
     */
    private HabitState $state;

    /**
     * @ORM\Column(type="creation_habit_state", length=32, nullable=true)
     */
    private ?CreationHabitState $creationState = null;

    /**
     * @ORM\Column(type="smallint")
     */
    private int $remindWeekDays = 0;

    /**
     * @ORM\Column(type="time_immutable", nullable=true)
     */
    private ?\DateTimeImmutable $remindAt = null;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->state = HabitState::get(HabitState::DRAFT);
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getState(): string
    {
        return (string) $this->state->getValue();
    }

    public function setState(string $state): void
    {
        $this->state = HabitState::get($state);
    }

    public function getCreationState(): string
    {
        return (string) $this->creationState->getValue();
    }

    public function setCreationState(string $creationState): void
    {
        $this->creationState = CreationHabitState::get($creationState);
    }

    public function isDraft(): bool
    {
        return $this->state->equals(HabitState::get(HabitState::DRAFT));
    }

    public function isPublished(): bool
    {
        return $this->state->equals(HabitState::get(HabitState::PUBLISHED));
    }

    public function getRemindWeekDays(): int
    {
        return $this->remindWeekDays;
    }

    public function setRemindWeekDays(int $remindWeekDays): void
    {
        $this->remindWeekDays = $remindWeekDays;
    }

    public function getRemindAt(): \DateTimeImmutable
    {
        return $this->remindAt;
    }

    public function setRemindAt(\DateTimeImmutable $remindAt): void
    {
        $this->remindAt = $remindAt;
    }
}
