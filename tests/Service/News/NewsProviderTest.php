<?php

declare(strict_types=1);

namespace App\Tests\Service\News;

use App\Service\News\NewsProvider;
use PHPUnit\Framework\TestCase;

class NewsProviderTest extends TestCase
{
    private NewsProvider $newsProvider;

    protected function setUp(): void
    {
        $this->newsProvider = new NewsProvider();
    }

    public function testGetLatest(): void
    {
        $latest = $this->newsProvider->getLatest();

        $this->assertSame(2, $latest->id);
    }

    public function testGetAll(): void
    {
        $all = $this->newsProvider->getAll();

        $this->assertCount(2, $all);
        $this->assertSame(1, $all[0]->id);
        $this->assertSame(2, $all[1]->id);
    }

    public function testGetNewsAfterId(): void
    {
        $news = $this->newsProvider->getNewsAfterId(1);

        $this->assertCount(1, $news);
        $this->assertSame(2, $news[0]->id);

        $empty = $this->newsProvider->getNewsAfterId(99);
        $this->assertCount(0, $empty);
    }
}
