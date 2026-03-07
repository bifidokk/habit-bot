<?php

declare(strict_types=1);

namespace App\Tests\Service\Command\Settings;

use App\Entity\User;
use App\Service\Command\CommandCallback;
use App\Service\Command\CommandCallbackEnum;
use App\Service\Command\Settings\LanguageFormCommand;
use App\Service\Keyboard\UserLanguageInlineKeyboard;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Type\InlineKeyboardMarkupType;
use TgBotApi\BotApiBase\Type\UpdateType;

class LanguageFormCommandTest extends TestCase
{
    private LanguageFormCommand $command;

    private BotApiComplete&MockObject $bot;

    private TranslatorInterface&MockObject $translator;

    private UserLanguageInlineKeyboard&MockObject $userLanguageInlineKeyboard;

    protected function setUp(): void
    {
        $this->bot = $this->createMock(BotApiComplete::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->userLanguageInlineKeyboard = $this->createMock(UserLanguageInlineKeyboard::class);

        $this->command = new LanguageFormCommand(
            $this->bot,
            $this->translator,
            $this->userLanguageInlineKeyboard,
        );
    }

    public function testCanRunWithCorrectCallback(): void
    {
        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::SettingsLanguageForm;

        $this->assertTrue($this->command->canRun(new UpdateType(), new User(), $callback));
    }

    public function testCanRunWithNull(): void
    {
        $this->assertFalse($this->command->canRun(new UpdateType(), new User(), null));
    }

    public function testRunEditsMessageWithLanguageKeyboard(): void
    {
        $user = new User();
        $update = $this->createCallbackUpdate(123, 456);

        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::SettingsLanguageForm;

        $this->translator->method('trans')->willReturn('Choose language');
        $this->userLanguageInlineKeyboard->method('generate')
            ->willReturn(InlineKeyboardMarkupType::create([]));

        $this->bot->expects($this->once())->method('editMessageText');

        $this->command->run($update, $user, $callback);
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
