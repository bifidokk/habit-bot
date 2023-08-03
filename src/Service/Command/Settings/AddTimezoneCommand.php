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
use App\Service\User\Event\TimezoneChangedEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\SendAnimationMethod;
use TgBotApi\BotApiBase\Method\SendMessageMethod;
use TgBotApi\BotApiBase\Type\UpdateType;

class AddTimezoneCommand extends AbstractCommand implements CommandInterface
{
    public const COMMAND_NAME = 'settings_add_timezone';

    public function __construct(
        private readonly BotApiComplete $bot,
        private readonly TranslatorInterface $translator,
        private readonly UserRepository $userRepository,
        private readonly Animation $animation,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {}

    public function canRun(UpdateType $update, User $user, ?CommandCallback $commandCallback): bool
    {
        return $commandCallback !== null
            && $commandCallback->command === CommandCallbackEnum::SetTimezone;
    }

    public function run(UpdateType $update, User $user, ?CommandCallback $commandCallback): void
    {
        if ($commandCallback === null) {
            return;
        }

        $timezone = $commandCallback->parameters['tz'] ?? 'UTC';

        try {
            $timezone = new \DateTimeZone($timezone);
        } catch (\Throwable) {
            return;
        }

        $user->setTimezone($timezone);
        $this->userRepository->save($user);
        $this->eventDispatcher->dispatch(new TimezoneChangedEvent($user));

        $this->bot->sendMessage(
            SendMessageMethod::create(
                $update->callbackQuery->message->chat->id,
                $this->translator->trans('command.response.settings_timezone')
            )
        );

        $this->bot->sendAnimation(SendAnimationMethod::create(
            $update->callbackQuery->message->chat->id,
            $this->animation->getByType(AnimationType::Timezone),
        ));
    }
}
