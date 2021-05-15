<?php

declare(strict_types=1);

namespace App\Service\Message;

class Animation
{
    private const ANIMATIONS = [
        AnimationType::SUCCESS => [
            'CgACAgQAAxkBAAIE3GCNfc0jdjnf4FGsWKc8clfHadSEAAIPjQACSxdkB9jABaAXoNhdHwQ',
        ],
        AnimationType::TIMEZONE => [
            'CgACAgQAAxkBAAIFmmCfvxkkzvAl7UTFN4S72KmntxfpAAJAAgACTQrdUgiuf6VQHXLeHwQ',
        ],
    ];

    public function getByType(AnimationType $animationType): string
    {
        if (!isset(self::ANIMATIONS[$animationType->getValue()])) {
            throw new \Exception(sprintf('Could not find animation with type %s', $animationType->getValue()));
        }

        $animations = self::ANIMATIONS[$animationType->getValue()];

        return $animations[array_rand($animations)];
    }
}
