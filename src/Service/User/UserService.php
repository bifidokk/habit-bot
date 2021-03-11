<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Entity\User;
use App\Repository\UserRepository;
use Elao\Enum\Enum;
use Symfony\Component\Workflow\StateMachine;
use TgBotApi\BotApiBase\Type\MessageType;
use TgBotApi\BotApiBase\Type\UserType;

class UserService
{
    private UserRepository $userRepository;
    private StateMachine $stateMachine;

    public function __construct(
        UserRepository $userRepository,
        StateMachine $stateMachine
    ) {
        $this->userRepository = $userRepository;
        $this->stateMachine = $stateMachine;
    }

    public function getUser(MessageType $message): ?User
    {
        $from = $message->from;

        if ($from === null) {
            return null;
        }

        $user = $this->findUserByTelegramId($from->id);

        if ($user === null) {
            $user = $this->createUser($from);
        }

        return $user;
    }

    public function changeUserState(User $user, Enum $state): void
    {
        $this->stateMachine->apply($user, (string)$state->getValue());
        $this->userRepository->save($user);
    }

    public function moveUserToStart(User $user): void
    {
        $user->setState((string)UserState::get(UserState::START)->getValue());
        $this->userRepository->save($user);
    }

    private function findUserByTelegramId(int $id): ?User
    {
        return $this->userRepository->findOneBy([
            'telegramId' => $id,
        ]);
    }

    private function createUser(UserType $userType): User
    {
        $user = User::createFromUserType($userType);
        $this->userRepository->save($user);

        return $user;
    }
}
