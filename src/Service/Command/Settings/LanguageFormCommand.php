<?php

declare(strict_types=1);

namespace App\Service\Command\Settings;

use App\Entity\User;
use App\Service\Command\AbstractCommand;
use App\Service\Command\CommandCallback;
use App\Service\Command\CommandCallbackEnum;
use App\Service\Command\CommandInterface;
use App\Service\Keyboard\UserLanguageInlineKeyboard;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\SendMessageMethod;
use TgBotApi\BotApiBase\Type\UpdateType;

class LanguageFormCommand extends AbstractCommand implements CommandInterface
{
    public const COMMAND_NAME = 'settings_language_form';

    private BotApiComplete $bot;
    private LoggerInterface $logger;
    private TranslatorInterface $translator;
    private UserLanguageInlineKeyboard $userLanguageInlineKeyboard;

    public function __construct(
        BotApiComplete $bot,
        LoggerInterface $logger,
        TranslatorInterface $translator,
        UserLanguageInlineKeyboard $userLanguageInlineKeyboard
    ) {
        $this->bot = $bot;
        $this->logger = $logger;
        $this->translator = $translator;
        $this->userLanguageInlineKeyboard = $userLanguageInlineKeyboard;
    }

    public function canRun(UpdateType $update, User $user, ?CommandCallback $commandCallback): bool
    {
        return $commandCallback !== null
            && $commandCallback->command->getValue() === CommandCallbackEnum::SETTINGS_LANGUAGE_FORM;
    }

    public function run(UpdateType $update, User $user, ?CommandCallback $commandCallback): void
    {
        $this->bot->sendMessage(
            SendMessageMethod::create(
                $update->callbackQuery->message->chat->id,
                $this->translator->trans('command.response.settings_language_form'),
                [
                    'replyMarkup' => $this->userLanguageInlineKeyboard->generate(),
                ]
            )
        );
    }
}
