<?php

declare(strict_types=1);

namespace App\Service\News;

class NewsProvider
{
    /**
     * @var News[]
     */
    private array $news = [];

    public function __construct()
    {
        $this->news = [
            new News(
                id: 1,
                ru: 'Привет! Теперь вы будете получать новости и обновления бота в этом чате.',
                en: "Hey! From now on you'll receive bot news and updates in this chat.",
            ),
            new News(
                id: 2,
                ru: "🎉 Новость!\n\nУ бота теперь есть своё мини-приложение прямо в Telegram! Там можно управлять привычками и смотреть свой прогресс — всё красиво и удобно 📊✨\n\nЖми кнопку Start в боте и заглядывай 👀",
                en: "🎉 News!\n\nThe bot now has its own mini app right inside Telegram! You can manage your habits and check your progress — all nice and tidy 📊✨\n\nHit the Start button in the bot and take a peek 👀",
            ),
        ];
    }

    /**
     * @return News[]
     */
    public function getAll(): array
    {
        return $this->news;
    }

    public function getLatest(): News
    {
        return $this->news[array_key_last($this->news)];
    }

    /**
     * @return News[]
     */
    public function getNewsAfterId(int $id): array
    {
        return array_values(
            array_filter($this->news, static fn (News $news) => $news->id > $id)
        );
    }
}
