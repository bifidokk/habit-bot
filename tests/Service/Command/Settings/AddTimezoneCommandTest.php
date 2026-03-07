<?php

declare(strict_types=1);

namespace App\Tests\Service\Command\Settings;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Command\CommandCallback;
use App\Service\Command\CommandCallbackEnum;
use App\Service\Command\Settings\AddTimezoneCommand;
use App\Service\Message\Animation;
use App\Service\Message\MessageContent;
use App\Service\User\Event\TimezoneChangedEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Type\UpdateType;

class AddTimezoneCommandTest extends TestCase
{
    private AddTimezoneCommand $command;

    private BotApiComplete&MockObject $bot;

    private TranslatorInterface&MockObject $translator;

    private UserRepository&MockObject $userRepository;

    private Animation&MockObject $animation;

    private EventDispatcherInterface&MockObject $eventDispatcher;

    private MessageContent&MockObject $messageContent;

    protected function setUp(): void
    {
        $this->bot = $this->createMock(BotApiComplete::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->animation = $this->createMock(Animation::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->messageContent = $this->createMock(MessageContent::class);

        $this->command = new AddTimezoneCommand(
            $this->bot,
            $this->translator,
            $this->userRepository,
            $this->animation,
            $this->eventDispatcher,
            $this->messageContent,
        );
    }

    public function testCanRunWithCorrectCallback(): void
    {
        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::SetTimezone;

        $this->assertTrue($this->command->canRun(new UpdateType(), new User(), $callback));
    }

    public function testCanRunWithNull(): void
    {
        $this->assertFalse($this->command->canRun(new UpdateType(), new User(), null));
    }

    public function testRunWithNullCallback(): void
    {
        $update = new UpdateType();
        $user = new User();

        $this->bot->expects($this->never())->method('editMessageText');

        $this->command->run($update, $user, null);
    }

    public function testRunWithValidTimezone(): void
    {
        $user = new User();
        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::SetTimezone;
        $callback->parameters = [
            'tz' => 'Europe/Moscow',
        ];

        $update = $this->createCallbackUpdate(123, 456);

        $this->userRepository->expects($this->once())->method('save')->with($user);

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(TimezoneChangedEvent::class));

        $this->translator->method('trans')->willReturn('Timezone set to Europe/Moscow');
        $this->messageContent->method('escapeMessageSymbols')->willReturn('Timezone set to Europe/Moscow');
        $this->animation->method('getByType')->willReturn('animation_id');

        $this->bot->expects($this->once())->method('editMessageText');
        $this->bot->expects($this->once())->method('sendAnimation');

        $this->command->run($update, $user, $callback);
    }

    public function testRunWithInvalidTimezone(): void
    {
        $user = new User();
        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::SetTimezone;
        $callback->parameters = [
            'tz' => 'Invalid/Zone',
        ];

        $update = new UpdateType();

        $this->userRepository->expects($this->never())->method('save');
        $this->bot->expects($this->never())->method('editMessageText');

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
