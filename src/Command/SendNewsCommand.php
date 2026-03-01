<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\UserRepository;
use App\Service\News\NewsProvider;
use App\Service\User\UserService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\SendMessageMethod;

#[AsCommand(name: 'app:send-news')]
class SendNewsCommand extends Command
{
    use LockableTrait;

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly BotApiComplete $bot,
        private readonly NewsProvider $newsProvider,
        private readonly LoggerInterface $logger,
        private readonly UserService $userService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (! $this->lock()) {
            $output->writeln('The command is already running in another process.');

            return Command::SUCCESS;
        }

        $latestNews = $this->newsProvider->getLatest();
        $users = $this->userRepository->findActiveUsersWithNewsIdLessThan($latestNews->id);

        $output->writeln(sprintf('Found %d users to notify', count($users)));

        foreach ($users as $user) {
            $newsToSend = $this->newsProvider->getNewsAfterId($user->getLastNewsId());

            foreach ($newsToSend as $news) {
                try {
                    $this->bot->sendMessage(
                        SendMessageMethod::create(
                            $user->getTelegramId(),
                            $news->getTextByLanguage($user->getLanguageCode()),
                        )
                    );
                } catch (\Throwable $exception) {
                    $this->logger->error('An error occurred during the news sending', [
                        'error' => $exception->getMessage(),
                        'code' => $exception->getCode(),
                    ]);

                    if ($this->isForbidden($exception->getMessage())) {
                        $this->userService->deactivateUser($user);
                    }

                    continue 2;
                }
            }

            $user->setLastNewsId($latestNews->id);
            $this->userRepository->save($user);
        }

        $this->release();

        return Command::SUCCESS;
    }

    private function isForbidden(string $message): bool
    {
        return str_contains($message, 'Forbidden');
    }
}
