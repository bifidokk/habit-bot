<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Entity\User;
use App\Repository\UserRepository;
use TgBotApi\BotApiBase\Type\UpdateType;
use TgBotApi\BotApiBase\Type\UserType;

class UserService
{
    public function __construct(
        private  UserRepository $userRepository
    ) {}

    public function getUser(UpdateType $update): ?User
    {
        $from = $update->message ? $update->message->from : $update->callbackQuery?->from;

        if ($from === null) {
            return null;
        }

        $user = $this->userRepository->findOneByTelegramId($from->id);

        if ($user === null) {
            $user = $this->createUser($from);
        }

        return $user;
    }

    private function createUser(UserType $userType): User
    {
        $user = User::createFromUserType($userType);
        $this->userRepository->save($user);

        return $user;
    }
}
