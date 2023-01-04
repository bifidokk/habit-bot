<?php

declare(strict_types=1);

namespace App\Service\Keyboard;

use App\Service\Command\CommandCallbackEnum;
use TgBotApi\BotApiBase\Type\InlineKeyboardButtonType;
use TgBotApi\BotApiBase\Type\InlineKeyboardMarkupType;

class UserTimezoneInlineKeyboard
{
    private const TIMEZONES = [
        'UTC', '+01:00', '+02:00', '+03:00', '+04:00', '+05:00',
        '+06:00', '+07:00', '+08:00', '+09:00', '+10:00', '+11:00',
        '+12:00', '-01:00', '-02:00', '-03:00', '-04:00', '-05:00',
        '-06:00', '-07:00', '-09:00', '-09:00', '-10:00', '-11:00',
    ];

    private const BUTTONS_IN_A_ROW = 4;

    public function generate(\DateTimeZone $dateTimeZone): InlineKeyboardMarkupType
    {
        $buttons = [];
        $count = 0;

        foreach (self::TIMEZONES as $timezone) {
            $rowNumber = floor($count / self::BUTTONS_IN_A_ROW);
            $timezoneLabel = $timezone;

            if ($dateTimeZone->getName() === $timezone) {
                $timezoneLabel = sprintf('%s%s', EmojiCode::Marked->value, $timezone);
            }

            $buttons[$rowNumber][] = InlineKeyboardButtonType::create(
                $timezoneLabel,
                [
                    'callbackData' => sprintf(
                        '%s?&tz=%s',
                        CommandCallbackEnum::SetTimezone->value,
                        urlencode($timezone)
                    ),
                ]
            );

            ++$count;
        }

        return InlineKeyboardMarkupType::create($buttons);
    }
}
