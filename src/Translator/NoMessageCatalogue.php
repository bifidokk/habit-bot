<?php

declare(strict_types=1);

namespace App\Translator;

use Symfony\Component\Config\Resource\ResourceInterface;
use Symfony\Component\Translation\MessageCatalogueInterface;

class NoMessageCatalogue implements MessageCatalogueInterface
{
    public function getLocale(): string
    {
        return '';
    }

    public function getDomains(): array
    {
        return [];
    }

    public function all(string $domain = null): array
    {
        return [];
    }

    public function set(string $id, string $translation, string $domain = 'messages'): void
    {
    }

    public function has(string $id, string $domain = 'messages'): bool
    {
        return true;
    }

    public function defines(string $id, string $domain = 'messages'): bool
    {
        return true;
    }

    public function get(string $id, string $domain = 'messages'): string
    {
        return '';
    }

    public function replace(array $messages, string $domain = 'messages'): void
    {
    }

    public function add(array $messages, string $domain = 'messages'): void
    {
    }

    public function addCatalogue(MessageCatalogueInterface $catalogue): void
    {
    }

    public function addFallbackCatalogue(MessageCatalogueInterface $catalogue): void
    {
    }

    public function getFallbackCatalogue(): ?MessageCatalogueInterface
    {
        return null;
    }

    public function getResources(): array
    {
        return [];
    }

    public function addResource(ResourceInterface $resource): void
    {
    }
}
