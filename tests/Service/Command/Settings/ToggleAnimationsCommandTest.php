<?php

declare(strict_types=1);

namespace App\Tests\Service\Command\Settings;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Command\CommandCallback;
use App\Service\Command\CommandCallbackEnum;
use App\Service\Command\Settings\ToggleAnimationsCommand;
use App\Service\Keyboard\SettingsInlineKeyboard;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Type\InlineKeyboardMarkupType;
use TgBotApi\BotApiBase\Type\UpdateType;

class ToggleAnimationsCommandTest extends TestCase
{
    private ToggleAnimationsCommand $command;

    private BotApiComplete&MockObject $bot;

    private TranslatorInterface&MockObject $translator;

    private UserRepository&MockObject $userRepository;

    private SettingsInlineKeyboard&MockObject $settingsInlineKeyboard;

    protected function setUp(): void
    {
        $this->bot = $this->createMock(BotApiComplete::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->settingsInlineKeyboard = $this->createMock(SettingsInlineKeyboard::class);

        $this->command = new ToggleAnimationsCommand(
            $this->bot,
            $this->translator,
            $this->userRepository,
            $this->settingsInlineKeyboard,
        );
    }

    public function testCanRunWithCorrectCallback(): void
    {
        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::ToggleAnimations;

        $this->assertTrue($this->command->canRun(new UpdateType(), new User(), $callback));
    }

    public function testCanRunWithWrongCallback(): void
    {
        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::HabitList;

        $this->assertFalse($this->command->canRun(new UpdateType(), new User(), $callback));
    }

    public function testCanRunWithNull(): void
    {
        $this->assertFalse($this->command->canRun(new UpdateType(), new User(), null));
    }

    public function testRunTogglesAnimationsOffAndSendsMessage(): void
    {
        $user = new User();
        $this->assertTrue($user->isShowAnimations());

        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::ToggleAnimations;

        $update = $this->createCallbackUpdate(123, 456);

        $this->userRepository->expects($this->once())->method('save')->with($user);

        $this->translator->method('trans')
            ->with('settings_menu.animations_off')
            ->willReturn('Animations disabled');

        $this->settingsInlineKeyboard->method('generate')
            ->willReturn(InlineKeyboardMarkupType::create([]));

        $this->bot->expects($this->once())->method('editMessageText');

        $this->command->run($update, $user, $callback);

        $this->assertFalse($user->isShowAnimations());
    }

    public function testRunTogglesAnimationsOnAndSendsMessage(): void
    {
        $user = new User();
        $user->toggleShowAnimations();
        $this->assertFalse($user->isShowAnimations());

        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::ToggleAnimations;

        $update = $this->createCallbackUpdate(123, 456);

        $this->userRepository->expects($this->once())->method('save')->with($user);

        $this->translator->method('trans')
            ->with('settings_menu.animations_on')
            ->willReturn('Animations enabled');

        $this->settingsInlineKeyboard->method('generate')
            ->willReturn(InlineKeyboardMarkupType::create([]));

        $this->bot->expects($this->once())->method('editMessageText');

        $this->command->run($update, $user, $callback);

        $this->assertTrue($user->isShowAnimations());
    }

    private function createCallbackUpdate(int $chatId, int $messageId): UpdateType
    {
        $update = new UpdateType();
        $update->callbackQuery = new \TgBotApi\BotApiBase\Type\CallbackQueryType();
        $update->callbackQuery->message = new \TgBotApi\BotApiBase\Type\MessageType();
        $update->callbackQuery->message->chat = new \TgBotApi\BotApiBase\Type\ChatType();
        $update->callbackQuery->message->chat->id = $chatId;
        $update->callbackQuery->message->messageId = $messageId;

        return $update;
    }
}
