<?php

declare(strict_types=1);

namespace App\Entity;

use App\Service\User\UserState;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidV4Generator;
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
    private string $id = '';

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
     *
     * @Assert\Length(max=64)
     */
    private UserState $state;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->state = UserState::get(UserState::START);
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): string
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
}
