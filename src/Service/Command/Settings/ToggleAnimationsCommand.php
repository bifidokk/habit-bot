<?php

declare(strict_types=1);

namespace App\Service\Command\Settings;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Command\AbstractCommand;
use App\Service\Command\CommandCallback;
use App\Service\Command\CommandCallbackEnum;
use App\Service\Command\CommandInterface;
use App\Service\Keyboard\SettingsInlineKeyboard;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\EditMessageTextMethod;
use TgBotApi\BotApiBase\Type\UpdateType;

class ToggleAnimationsCommand extends AbstractCommand implements CommandInterface
{
    public const COMMAND_NAME = 'settings_toggle_animations';

    public function __construct(
        private readonly BotApiComplete $bot,
        private readonly TranslatorInterface $translator,
        private readonly UserRepository $userRepository,
        private readonly SettingsInlineKeyboard $settingsInlineKeyboard,
    ) {
    }

    public function canRun(UpdateType $update, User $user, ?CommandCallback $commandCallback): bool
    {
        return $commandCallback !== null
            && $commandCallback->command === CommandCallbackEnum::ToggleAnimations;
    }

    public function run(UpdateType $update, User $user, ?CommandCallback $commandCallback): void
    {
        $user->toggleShowAnimations();
        $this->userRepository->save($user);

        $statusKey = $user->isShowAnimations()
            ? 'settings_menu.animations_on'
            : 'settings_menu.animations_off';

        $this->bot->editMessageText(
            EditMessageTextMethod::create(
                $update->callbackQuery->message->chat->id,
                $update->callbackQuery->message->messageId,
                $this->translator->trans($statusKey),
                [
                    'replyMarkup' => $this->settingsInlineKeyboard->generate($user),
                ]
            )
        );
    }
}
