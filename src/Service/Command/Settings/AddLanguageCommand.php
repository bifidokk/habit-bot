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
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\SendAnimationMethod;
use TgBotApi\BotApiBase\Method\SendMessageMethod;
use TgBotApi\BotApiBase\Type\UpdateType;

class AddLanguageCommand extends AbstractCommand implements CommandInterface
{
    public const COMMAND_NAME = 'settings_add_language';

    public function __construct(
        private BotApiComplete $bot,
        private TranslatorInterface $translator,
        private UserRepository $userRepository,
        private Animation $animation,
        private MainMenuKeyboard $mainMenuKeyboard,
    ) {}

    public function canRun(UpdateType $update, User $user, ?CommandCallback $commandCallback): bool
    {
        return $commandCallback !== null
            && $commandCallback->command->getValue() === CommandCallbackEnum::SET_LANGUAGE;
    }

    public function run(UpdateType $update, User $user, ?CommandCallback $commandCallback): void
    {
        if ($commandCallback === null) {
            return;
        }

        $language = $commandCallback->parameters['lang'] ?? 'en';
        $user->setLanguageCode($language);
        $this->userRepository->save($user);

        $this->bot->sendMessage(
            SendMessageMethod::create(
                $update->callbackQuery->message->chat->id,
                $this->translator->trans('command.response.settings_language', [], null, $language), [
                    'replyMarkup' => $this->mainMenuKeyboard->generate($language),
                ])
        );

        $this->bot->sendAnimation(SendAnimationMethod::create(
            $update->callbackQuery->message->chat->id,
            $this->animation->getByType(AnimationType::get(AnimationType::LANGUAGE)),
        ));
    }
}
