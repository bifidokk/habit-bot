<?php

declare(strict_types=1);

namespace App\Service\Message;

class Animation
{
    public function getByType(AnimationType $animationType): string
    {
        $animationList = $this->getAnimationsList();

        if (! isset($animationList[$animationType->value])) {
            throw new \Exception(sprintf('Could not find animation with type %s', $animationType->value));
        }

        $animations = $animationList[$animationType->value];

        return $animations[array_rand($animations)];
    }

    private function getAnimationsList(): array
    {
        return [
            AnimationType::Success->value => [
                'CgACAgQAAxkBAAIE3GCNfc0jdjnf4FGsWKc8clfHadSEAAIPjQACSxdkB9jABaAXoNhdHwQ',
            ],
            AnimationType::Timezone->value => [
                'CgACAgQAAxkBAAIFmmCfvxkkzvAl7UTFN4S72KmntxfpAAJAAgACTQrdUgiuf6VQHXLeHwQ',
            ],
            AnimationType::Language->value => [
                'CgACAgIAAxkBAAIFwmChNqGiuocZE5rdbbFI8IYgFmQiAALWAQACp0bwSGHuSU9MoAPrHwQ',
            ],
            AnimationType::Removed->value => [
                'CgACAgQAAxkBAAIGVmC7cyN8qIvfr-o_byy9LSeTiwRmAAIsAgACssyUUgWYMi7fhfreHwQ',
            ],
            AnimationType::HabitDone->value => [
                'CgACAgQAAxkBAAIIlmDYPIS-4ZJ3artXPwIGBs8KrsFZAAJjAgACJ8yMUk22cJ-a6bzEIAQ',
            ],
            AnimationType::HabitBusy->value => [
                'CgACAgQAAxkBAAIIlWDYO1BFF6skqWqiQfAnlrERPaRdAAIjAgACZO2VUiwBUn2jjkkLIAQ',
            ],
        ];
    }
}
