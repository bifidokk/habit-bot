<?php

declare(strict_types=1);

namespace App\Translator;

use Symfony\Component\Translation\TranslatorBagInterface;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class NoTranslator implements TranslatorInterface, TranslatorBagInterface, LocaleAwareInterface
{
    public function trans(string $id, array $parameters = [], string $domain = null, string $locale = null)
    {
        return $id;
    }

    public function getCatalogue(string $locale = null)
    {
        return new NoMessageCatalogue();
    }

    public function setLocale(string $locale): void
    {
    }

    public function getLocale()
    {
        return '';
    }
}