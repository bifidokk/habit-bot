<?php

declare(strict_types=1);

namespace App\Service\Command\Settings;

use App\Entity\User;
use App\Service\Command\AbstractCommand;
use App\Service\Command\CommandCallback;
use App\Service\Command\CommandInterface;
use App\Service\Keyboard\EmojiCode;
use App\Service\Keyboard\SettingsInlineKeyboard;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\SendMessageMethod;
use TgBotApi\BotApiBase\Type\UpdateType;

class SettingsCommand extends AbstractCommand implements CommandInterface
{
    public const COMMAND_NAME = 'settings_menu';

    private BotApiComplete $bot;
    private LoggerInterface $logger;
    private SettingsInlineKeyboard $settingsInlineKeyboard;
    private TranslatorInterface $translator;

    public function __construct(
        BotApiComplete $bot,
        LoggerInterface $logger,
        SettingsInlineKeyboard $settingsInlineKeyboard,
        TranslatorInterface $translator
    ) {
        $this->bot = $bot;
        $this->logger = $logger;
        $this->settingsInlineKeyboard = $settingsInlineKeyboard;
        $this->translator = $translator;
    }

    public function canRun(UpdateType $update, User $user, ?CommandCallback $commandCallback): bool
    {
        return $update->message !== null
            && $update->message->text === sprintf(
                '%s %s',
                EmojiCode::SETTINGS,
                $this->translator->trans('settings')
            );
    }

    public function run(UpdateType $update, User $user, ?CommandCallback $commandCallback): void
    {
        $this->bot->sendMessage(
            SendMessageMethod::create(
                $update->message->chat->id,
                $update->message->text, [
                    'replyMarkup' => $this->settingsInlineKeyboard->generate(),
                ])
        );
    }
}
