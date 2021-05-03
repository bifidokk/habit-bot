<?php

declare(strict_types=1);

namespace App\Translator;

use Symfony\Contracts\Translation\TranslatorInterface;

class NoTranslator implements TranslatorInterface
{
    public function trans(string $id, array $parameters = [], string $domain = null, string $locale = null)
    {
        return $id;
    }
}
