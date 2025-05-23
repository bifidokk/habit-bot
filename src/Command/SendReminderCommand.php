<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Habit;
use App\Repository\HabitRepository;
use App\Service\Habit\RemindService;
use App\Service\Keyboard\HabitDoneKeyboard;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\SendMessageMethod;

#[AsCommand(name: 'app:send-reminder')]
class SendReminderCommand extends Command
{
    use LockableTrait;

    public function __construct(
        private readonly HabitRepository $habitRepository,
        private readonly BotApiComplete $bot,
        private readonly RemindService $remindService,
        private readonly HabitDoneKeyboard $habitDoneKeyboard,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (! $this->lock()) {
            $output->writeln('The command is already running in another process.');

            return Command::SUCCESS;
        }

        $habits = $this->habitRepository->findReadyForRemindHabits();

        /** @var Habit $habit */
        foreach ($habits as $habit) {
            try {
                $this->bot->sendMessage(
                    SendMessageMethod::create(
                        $habit->getUser()->getTelegramId(),
                        $habit->getDescription(),
                        [
                            'replyMarkup' => $this->habitDoneKeyboard->generate($habit),
                        ]
                    )
                );
            } catch (\Throwable $exception) {
                $this->logger->error('An error occurred during the reminder sending', [
                    'error' => $exception->getMessage(),
                    'code' => $exception->getCode(),
                ]);

                continue;
            }

            $habit->setNextRemindAt($this->remindService->getNextRemindTime(new \DateTimeImmutable(), $habit));
            $this->habitRepository->save($habit);
        }

        $this->release();

        return Command::SUCCESS;
    }
}
