<?php

declare(strict_types=1);

namespace App\Tests\Service\News;

use App\Service\News\News;
use PHPUnit\Framework\TestCase;

class NewsTest extends TestCase
{
    public function testGetTextByLanguageRu(): void
    {
        $news = new News(id: 1, ru: 'Привет', en: 'Hello');

        $this->assertSame('Привет', $news->getTextByLanguage('ru'));
    }

    public function testGetTextByLanguageEn(): void
    {
        $news = new News(id: 1, ru: 'Привет', en: 'Hello');

        $this->assertSame('Hello', $news->getTextByLanguage('en'));
    }

    public function testGetTextByLanguageNull(): void
    {
        $news = new News(id: 1, ru: 'Привет', en: 'Hello');

        $this->assertSame('Hello', $news->getTextByLanguage(null));
    }
}
