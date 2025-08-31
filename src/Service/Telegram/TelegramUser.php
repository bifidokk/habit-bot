<?php

declare(strict_types=1);

namespace App\Service\Telegram;

class TelegramUser
{
    public function __construct(
        private readonly string $id,
        private readonly string $firstName,
        private readonly string $lastName,
        private readonly string $username,
        private readonly string $languageCode,
        private readonly string $photoUrl,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getLanguageCode(): string
    {
        return $this->languageCode;
    }

    public function getPhotoUrl(): string
    {
        return $this->photoUrl;
    }
}
