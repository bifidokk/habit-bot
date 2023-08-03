<?php

declare(strict_types=1);

namespace App\Service\Command\Settings;

use App\Entity\User;
use App\Service\Command\AbstractCommand;
use App\Service\Command\CommandCallback;
use App\Service\Command\CommandCallbackEnum;
use App\Service\Command\CommandInterface;
use App\Service\Keyboard\UserTimezoneInlineKeyboard;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\SendMessageMethod;
use TgBotApi\BotApiBase\Type\UpdateType;

class TimezoneFormCommand extends AbstractCommand implements CommandInterface
{
    public const COMMAND_NAME = 'settings_timezone_form';

    public function __construct(
        private readonly BotApiComplete $bot,
        private readonly TranslatorInterface $translator,
        private readonly UserTimezoneInlineKeyboard $userTimezoneInlineKeyboard,
    ) {}

    public function canRun(UpdateType $update, User $user, ?CommandCallback $commandCallback): bool
    {
        return $commandCallback !== null
            && $commandCallback->command === CommandCallbackEnum::SettingsTimezoneForm;
    }

    public function run(UpdateType $update, User $user, ?CommandCallback $commandCallback): void
    {
        $this->bot->sendMessage(
            SendMessageMethod::create(
                $update->callbackQuery->message->chat->id,
                $this->translator->trans('command.response.settings_timezone_form'),
                [
                    'replyMarkup' => $this->userTimezoneInlineKeyboard->generate($user->getTimezone()),
                ]
            )
        );
    }
}
