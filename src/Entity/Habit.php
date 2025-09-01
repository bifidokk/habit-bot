<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\HabitRepository;
use App\Service\Habit\HabitState;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: HabitRepository::class)]
#[ORM\Index(columns: ['next_remind_at'])]
class Habit
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?Uuid $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'habits')]
    private ?User $user = null;

    #[ORM\Column(type: 'string')]
    private string $description = '';

    #[ORM\Column(length: 32, enumType: HabitState::class, options: [
        'default' => 'draft',
    ])]
    private HabitState $state;

    #[ORM\Column(type: 'smallint')]
    private int $remindWeekDays = 0;

    #[ORM\Column(type: 'time_immutable', nullable: true)]
    private ?\DateTimeImmutable $remindAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $nextRemindAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\OneToMany(mappedBy: 'habit', targetEntity: Metric::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy([
        'createdAt' => 'DESC',
    ])]
    private Collection $metrics;

    public function __construct()
    {
        $this->state = HabitState::Draft;
        $this->createdAt = new \DateTimeImmutable();
        $this->metrics = new ArrayCollection();
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
        return $this->state === HabitState::Draft;
    }

    public function isPublished(): bool
    {
        return $this->state === HabitState::Published;
    }

    public function getRemindWeekDays(): int
    {
        return $this->remindWeekDays;
    }

    public function setRemindWeekDays(int $remindWeekDays): void
    {
        $this->remindWeekDays = $remindWeekDays;
    }

    /**
     * Returns array with filled number of days from Mon to Sun
     * [0, 2, 5] means Mon, Wed, Sat
     */
    public function getRemindWeekDaysArray(): array
    {
        $binaryArray = str_split(sprintf('%07d', decbin($this->getRemindWeekDays())));

        return array_keys(array_filter($binaryArray, fn ($v) => (int) $v === 1));
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
        $this->state = HabitState::Published;
    }

    public function getQueryParameter(): string
    {
        return sprintf('id=%s', $this->id ? $this->id->toRfc4122() : '');
    }

    public function setNextRemindAt(?\DateTimeImmutable $nextRemindAt): void
    {
        $this->nextRemindAt = $nextRemindAt;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
