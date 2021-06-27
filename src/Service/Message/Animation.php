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
        AnimationType::LANGUAGE => [
            'CgACAgIAAxkBAAIFwmChNqGiuocZE5rdbbFI8IYgFmQiAALWAQACp0bwSGHuSU9MoAPrHwQ',
        ],
        AnimationType::NOT_REMOVED => [
            'CgACAgIAAxkBAAIGVGC7cp0e3iWve5_8TOIFE7NC02g0AAIpAAN2kGBIg9YgAAEpdgHjHwQ',
        ],
        AnimationType::REMOVED => [
            'CgACAgQAAxkBAAIGVmC7cyN8qIvfr-o_byy9LSeTiwRmAAIsAgACssyUUgWYMi7fhfreHwQ',
        ],
        AnimationType::HABIT_DONE => [
            'CgACAgQAAxkBAAIIlmDYPIS-4ZJ3artXPwIGBs8KrsFZAAJjAgACJ8yMUk22cJ-a6bzEIAQ',
        ],
        AnimationType::HABIT_BUSY => [
            'CgACAgQAAxkBAAIIlWDYO1BFF6skqWqiQfAnlrERPaRdAAIjAgACZO2VUiwBUn2jjkkLIAQ',
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
