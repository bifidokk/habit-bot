<?php

declare(strict_types=1);

namespace App\Tests\Service\Command\Settings;

use App\Entity\User;
use App\Service\Command\CommandCallback;
use App\Service\Command\CommandCallbackEnum;
use App\Service\Command\Settings\TimezoneFormCommand;
use App\Service\Keyboard\UserTimezoneInlineKeyboard;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Type\InlineKeyboardMarkupType;
use TgBotApi\BotApiBase\Type\UpdateType;

class TimezoneFormCommandTest extends TestCase
{
    private TimezoneFormCommand $command;

    private BotApiComplete&MockObject $bot;

    private TranslatorInterface&MockObject $translator;

    private UserTimezoneInlineKeyboard&MockObject $userTimezoneInlineKeyboard;

    protected function setUp(): void
    {
        $this->bot = $this->createMock(BotApiComplete::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->userTimezoneInlineKeyboard = $this->createMock(UserTimezoneInlineKeyboard::class);

        $this->command = new TimezoneFormCommand(
            $this->bot,
            $this->translator,
            $this->userTimezoneInlineKeyboard,
        );
    }

    public function testCanRunWithCorrectCallback(): void
    {
        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::SettingsTimezoneForm;

        $this->assertTrue($this->command->canRun(new UpdateType(), new User(), $callback));
    }

    public function testCanRunWithNull(): void
    {
        $this->assertFalse($this->command->canRun(new UpdateType(), new User(), null));
    }

    public function testRunEditsMessageWithTimezoneKeyboard(): void
    {
        $user = new User();
        $user->setTimezone(new \DateTimeZone('UTC'));

        $update = $this->createCallbackUpdate(123, 456);

        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::SettingsTimezoneForm;

        $this->translator->method('trans')->willReturn('Choose timezone');
        $this->userTimezoneInlineKeyboard->method('generate')
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
