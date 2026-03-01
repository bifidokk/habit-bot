<?php

declare(strict_types=1);

namespace App\Service\News;

class News
{
    public function __construct(
        public readonly int $id,
        public readonly string $ru,
        public readonly string $en,
    ) {
    }

    public function getTextByLanguage(?string $languageCode): string
    {
        return $languageCode === 'ru' ? $this->ru : $this->en;
    }
}
