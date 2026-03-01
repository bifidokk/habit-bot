<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\UserRepository;
use App\Service\News\NewsProvider;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\SendMessageMethod;

#[AsCommand(name: 'app:send-news-preview')]
class SendNewsPreviewCommand extends Command
{
    private const PREVIEW_USER_UUID = '1ec5b314-8448-6d8e-bbbf-5f93a3dd1d07';

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly BotApiComplete $bot,
        private readonly NewsProvider $newsProvider,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $user = $this->userRepository->find(self::PREVIEW_USER_UUID);

        if ($user === null) {
            $output->writeln('Preview user not found');

            return Command::FAILURE;
        }

        $latestNews = $this->newsProvider->getLatest();
        $text = $latestNews->getTextByLanguage($user->getLanguageCode());

        $this->bot->sendMessage(
            SendMessageMethod::create(
                $user->getTelegramId(),
                $text,
            )
        );

        $output->writeln('Preview sent successfully');

        return Command::SUCCESS;
    }
}
