<?php

declare(strict_types=1);

namespace App\Entity;

use App\Service\User\UserState;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidV4Generator;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use TgBotApi\BotApiBase\Type\UserType;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidV4Generator::class)
     */
    private ?Uuid $id = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Assert\Length(max=255)
     */
    private ?string $username = null;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank()
     * @Assert\Length(max=255)
     */
    private string $firstName = '';

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Assert\Length(max=255)
     */
    private ?string $lastName = null;

    /**
     * @ORM\Column(type="integer", unique=true)
     *
     * @Assert\NotBlank()
     */
    private int $telegramId = 0;

    /**
     * @ORM\Column(type="string", length=3, nullable=true)
     *
     * @Assert\Length(max=3)
     */
    private ?string $languageCode = null;

    /**
     * @ORM\Column(type="user_state", length=64, options={"default"="start"})
     */
    private UserState $state;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private \DateTimeImmutable $createdAt;

    /**
     * @var Habit[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Habit", mappedBy="user", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"createdAt"="DESC"})
     */
    private $habits;

    public function __construct()
    {
        $this->state = UserState::get(UserState::START);
        $this->createdAt = new \DateTimeImmutable();
        $this->habits = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getState(): string
    {
        return (string) $this->state->getValue();
    }

    public function setState(string $state): void
    {
        $this->state = UserState::get($state);
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

    public function inHabitCreationFlow(): bool
    {
        return $this->state->equals(UserState::get(UserState::NEW_HABIT));
    }

    public function getDraftHabit(): ?Habit
    {
        $draftHabits = $this->habits->filter(
            function ($habit) {
                return $habit->isDraft();
            }
        );

        if ($draftHabits->count() === 0) {
            return null;
        }

        return $draftHabits->first();
    }

    public function getLastPublishedHabit(): ?Habit
    {
        $publishedHabits = $this->habits->filter(
            function ($habit) {
                return $habit->isPublished();
            }
        );

        if ($publishedHabits->count() === 0) {
            return null;
        }

        return $publishedHabits->first();
    }

    public function addHabit(Habit $habit): void
    {
        $this->habits->add($habit);
    }

    public function getTelegramId(): int
    {
        return $this->telegramId;
    }
}
