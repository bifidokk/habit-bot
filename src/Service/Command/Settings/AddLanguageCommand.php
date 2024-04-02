<?php

declare(strict_types=1);

namespace App\Service\Command\Settings;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Command\AbstractCommand;
use App\Service\Command\CommandCallback;
use App\Service\Command\CommandCallbackEnum;
use App\Service\Command\CommandInterface;
use App\Service\Keyboard\MainMenuKeyboard;
use App\Service\Message\Animation;
use App\Service\Message\AnimationType;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\DeleteMessageMethod;
use TgBotApi\BotApiBase\Method\SendAnimationMethod;
use TgBotApi\BotApiBase\Method\SendMessageMethod;
use TgBotApi\BotApiBase\Type\UpdateType;

class AddLanguageCommand extends AbstractCommand implements CommandInterface
{
    public const COMMAND_NAME = 'settings_add_language';

    private const DEFAULT_LANGUAGE = 'en';

    public function __construct(
        private readonly BotApiComplete $bot,
        private readonly TranslatorInterface $translator,
        private readonly UserRepository $userRepository,
        private readonly Animation $animation,
        private readonly MainMenuKeyboard $mainMenuKeyboard,
    ) {
    }

    public function canRun(UpdateType $update, User $user, ?CommandCallback $commandCallback): bool
    {
        return $commandCallback !== null
            && $commandCallback->command === CommandCallbackEnum::SetLanguage;
    }

    public function run(UpdateType $update, User $user, ?CommandCallback $commandCallback): void
    {
        if ($commandCallback === null) {
            return;
        }

        $language = $commandCallback->parameters['lang'] ?? self::DEFAULT_LANGUAGE;
        $user->setLanguageCode($language);
        $this->userRepository->save($user);

        $this->bot->deleteMessage(
            DeleteMessageMethod::create(
                $update->callbackQuery->message->chat->id,
                $update->callbackQuery->message->messageId,
            )
        );

        $this->bot->sendMessage(
            SendMessageMethod::create(
                $update->callbackQuery->message->chat->id,
                $this->translator->trans('command.response.settings_language', locale: $language),
                [
                    'replyMarkup' => $this->mainMenuKeyboard->generate($language),
                ]
            )
        );

        $this->bot->sendAnimation(SendAnimationMethod::create(
            $update->callbackQuery->message->chat->id,
            $this->animation->getByType(AnimationType::Language),
        ));
    }
}
