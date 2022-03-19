<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use TgBotApi\BotApiBase\Type\UserType;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: "users")]
class User
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid", unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?Uuid $id = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $username = null;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $firstName = '';

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $lastName = null;

    #[ORM\Column(type: "integer", unique: true)]
    #[Assert\NotBlank]
    private int $telegramId = 0;

    #[ORM\Column(type: "string", length: 3, nullable: true)]
    #[Assert\Length(max: 3)]
    private ?string $languageCode = null;

    #[ORM\Column(type: "datetime_immutable")]
    private \DateTimeImmutable $createdAt;

    #[ORM\OneToMany(mappedBy: "user", targetEntity: Habit::class, cascade: ["persist", "remove"], orphanRemoval: true)]
    #[ORM\OrderBy(["createdAt" => "DESC"])]
    private Collection $habits;

    #[ORM\Column(type: "string", length: 8)]
    private string $timezone = 'UTC';

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->habits = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public static function createFromUserType(UserType $userType): User
    {
        $user = new User();
        $user->username = $userType->username;
        $user->firstName = $userType->firstName;
        $user->lastName = $userType->lastName;
        $user->languageCode = $userType->languageCode;
        $user->telegramId = $userType->id;

        return $user;
    }

    public function addHabit(Habit $habit): void
    {
        $this->habits->add($habit);
    }

    public function getTelegramId(): int
    {
        return $this->telegramId;
    }

    public function getTimezone(): \DateTimeZone
    {
        return new \DateTimeZone($this->timezone);
    }

    public function setTimezone(\DateTimeZone $timezone): void
    {
        $this->timezone = $timezone->getName();
    }

    public function getLanguageCode(): ?string
    {
        return $this->languageCode;
    }

    public function setLanguageCode(?string $languageCode): void
    {
        $this->languageCode = $languageCode;
    }

    public function getPublishedHabits(): array
    {
        $publishedHabits = $this->habits->filter(
            function ($habit) {
                return $habit->isPublished();
            }
        );

        return $publishedHabits->toArray();
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }
}
