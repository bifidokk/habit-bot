<?php

declare(strict_types=1);

namespace App\Service\Command\Settings;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Command\AbstractCommand;
use App\Service\Command\CommandCallback;
use App\Service\Command\CommandCallbackEnum;
use App\Service\Command\CommandInterface;
use App\Service\Message\Animation;
use App\Service\Message\AnimationType;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\SendAnimationMethod;
use TgBotApi\BotApiBase\Method\SendMessageMethod;
use TgBotApi\BotApiBase\Type\UpdateType;

class AddTimezoneCommand extends AbstractCommand implements CommandInterface
{
    public const COMMAND_NAME = 'settings_add_timezone';
    private BotApiComplete $bot;
    private LoggerInterface $logger;
    private TranslatorInterface $translator;
    private UserRepository $userRepository;
    private Animation $animation;

    public function __construct(
        BotApiComplete $bot,
        LoggerInterface $logger,
        TranslatorInterface $translator,
        UserRepository $userRepository,
        Animation $animation
    ) {
        $this->bot = $bot;
        $this->logger = $logger;
        $this->translator = $translator;
        $this->userRepository = $userRepository;
        $this->animation = $animation;
    }

    public function canRun(UpdateType $update, User $user, ?CommandCallback $commandCallback): bool
    {
        return $commandCallback !== null
            && $commandCallback->command->getValue() === CommandCallbackEnum::SET_TIMEZONE;
    }

    public function run(UpdateType $update, User $user, ?CommandCallback $commandCallback): void
    {
        if ($commandCallback === null) {
            return;
        }

        $timezone = $commandCallback->parameters['tz'] ?? 'UTC';

        try {
            $timezone = new \DateTimeZone($timezone);
        } catch (\Throwable $exception) {
            return;
        }

        $user->setTimezone($timezone->getName());
        $this->userRepository->save($user);

        $this->bot->sendMessage(
            SendMessageMethod::create(
                $update->callbackQuery->message->chat->id,
                $this->translator->trans('command.response.settings_timezone')
            )
        );

        $this->bot->sendAnimation(SendAnimationMethod::create(
            $update->callbackQuery->message->chat->id,
            $this->animation->getByType(AnimationType::get(AnimationType::TIMEZONE)),
        ));
    }
}
