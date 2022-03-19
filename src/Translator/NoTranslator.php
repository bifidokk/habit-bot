<?php

declare(strict_types=1);

namespace App\Translator;

use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\MessageCatalogueInterface;
use Symfony\Component\Translation\TranslatorBagInterface;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class NoTranslator implements TranslatorInterface, TranslatorBagInterface, LocaleAwareInterface
{
    /** @var MessageCatalogue[] */
    private array $catalogues = [];

    public function trans(string $id, array $parameters = [], string $domain = null, string $locale = null): string
    {
        return $id;
    }

    public function getCatalogue(string $locale = null): MessageCatalogueInterface
    {
        return new NoMessageCatalogue();
    }

    public function setLocale(string $locale): void
    {
    }

    public function getLocale(): string
    {
        return '';
    }

    public function getCatalogues(): array
    {
        return array_values($this->catalogues);
    }
}
