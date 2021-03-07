<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Entity\User;
use App\Repository\UserRepository;
use TgBotApi\BotApiBase\Type\UserType;

class UserService
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function findUserByTelegramId(int $id): ?User
    {
        return $this->userRepository->findOneBy([
            'telegramId' => $id,
        ]);
    }

    public function createUser(UserType $userType): User
    {
        $user = User::createFromUserType($userType);
        $this->userRepository->save($user);

        return $user;
    }
}
