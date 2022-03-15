<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Habit;
use App\Repository\HabitRepository;
use App\Service\Habit\RemindService;
use App\Service\Keyboard\HabitDoneKeyboard;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\SendMessageMethod;

class SendReminderCommand extends Command
{
    use LockableTrait;

    protected static $defaultName = 'app:send-reminder';

    public function __construct(
        private HabitRepository $habitRepository,
        private BotApiComplete $bot,
        private RemindService $remindService,
        private HabitDoneKeyboard $habitDoneKeyboard,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->lock()) {
            $output->writeln('The command is already running in another process.');

            return Command::SUCCESS;
        }

        $habits = $this->habitRepository->findReadyForRemindHabits();

        /** @var Habit $habit */
        foreach ($habits as $habit) {
            $this->bot->sendMessage(
                SendMessageMethod::create(
                    $habit->getUser()->getTelegramId(),
                    $habit->getDescription(),
                    [
                        'replyMarkup' => $this->habitDoneKeyboard->generate($habit),
                    ]
                )
            );

            $habit->setNextRemindAt($this->remindService->getNextRemindTime(new \DateTimeImmutable(), $habit));
            $this->habitRepository->save($habit);
        }

        $this->release();

        return Command::SUCCESS;
    }
}
