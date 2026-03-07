<?php

declare(strict_types=1);

namespace App\Tests\Service\Command\Settings;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Command\CommandCallback;
use App\Service\Command\CommandCallbackEnum;
use App\Service\Command\Settings\AddLanguageCommand;
use App\Service\Keyboard\MainMenuKeyboard;
use App\Service\Message\Animation;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Type\UpdateType;

class AddLanguageCommandTest extends TestCase
{
    private AddLanguageCommand $command;

    private BotApiComplete&MockObject $bot;

    private TranslatorInterface&MockObject $translator;

    private UserRepository&MockObject $userRepository;

    private Animation&MockObject $animation;

    private MainMenuKeyboard&MockObject $mainMenuKeyboard;

    protected function setUp(): void
    {
        $this->bot = $this->createMock(BotApiComplete::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->animation = $this->createMock(Animation::class);
        $this->mainMenuKeyboard = $this->createMock(MainMenuKeyboard::class);

        $this->command = new AddLanguageCommand(
            $this->bot,
            $this->translator,
            $this->userRepository,
            $this->animation,
            $this->mainMenuKeyboard,
        );
    }

    public function testCanRunWithCorrectCallback(): void
    {
        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::SetLanguage;

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

        $this->bot->expects($this->never())->method('deleteMessage');

        $this->command->run($update, $user, null);
    }

    public function testRunSetsLanguageAndSendsMessages(): void
    {
        $user = new User();
        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::SetLanguage;
        $callback->parameters = [
            'lang' => 'ru',
        ];

        $update = $this->createCallbackUpdate(123, 456);

        $this->userRepository->expects($this->once())->method('save')->with($user);

        $this->translator->method('trans')->willReturn('Language set');
        $this->animation->method('getByType')->willReturn('animation_id');

        $this->bot->expects($this->once())->method('deleteMessage');
        $this->bot->expects($this->once())->method('sendMessage');
        $this->bot->expects($this->once())->method('sendAnimation');

        $this->command->run($update, $user, $callback);

        $this->assertSame('ru', $user->getLanguageCode());
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
