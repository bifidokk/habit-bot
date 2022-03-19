<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\HabitRepository;
use App\Service\Habit\HabitState;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: HabitRepository::class)]
#[ORM\Index(columns: ["next_remind_at"])]
class Habit
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid", unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?Uuid $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "habits")]
    private ?User $user = null;

    #[ORM\Column(type: "string")]
    private string $description = '';

    #[ORM\Column(type: "habit_state", length: 32, options: ["default" => "draft"])]
    private HabitState $state;

    #[ORM\Column(type: "smallint")]
    private int $remindWeekDays = 0;

    #[ORM\Column(type: "time_immutable", nullable: true)]
    private ?\DateTimeImmutable $remindAt = null;

    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    private ?\DateTimeImmutable $nextRemindAt = null;

    #[ORM\Column(type: "datetime_immutable")]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->state = HabitState::get(HabitState::DRAFT);
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
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

    public function getRemindAt(): ?\DateTimeImmutable
    {
        return $this->remindAt;
    }

    public function setRemindAt(\DateTimeImmutable $remindAt): void
    {
        $this->remindAt = $remindAt;
    }

    public function readyForPublishing(): bool
    {
        return $this->description !== ''
            && $this->remindWeekDays > 0
            && $this->remindAt instanceof \DateTimeImmutable;
    }

    public function publish(): void
    {
        $this->state = HabitState::get(HabitState::PUBLISHED);
    }

    public function getQueryParameter(): string
    {
        return sprintf('id=%s', $this->id ? $this->id->toRfc4122() : '');
    }

    public function setNextRemindAt(?\DateTimeImmutable $nextRemindAt): void
    {
        $this->nextRemindAt = $nextRemindAt;
    }
}
