<?php

declare(strict_types=1);

namespace App\Tests\Functional;

class WebhookDataFactory
{
    public static function getStartCommandData(): string
    {
        $data = self::getCommonData();

        $data['message']['text'] = '/start';
        $data['message']['entities'] = [
            0 => [
                'offset' => 0,
                'length' => 6,
                'type' => 'bot_command',
            ],
        ];

        return json_encode($data);
    }

    public static function getHabitCreationStartCommandData(): string
    {
        $data = self::getCommonData();
        $data['message']['text'] = 'Add a new habit';

        return json_encode($data);
    }

    public static function getHabitCreationAddTitleCommandData(): string
    {
        $data = self::getCommonData();
        $data['message']['text'] = 'This a new habit';

        return json_encode($data);
    }

    public static function getEmptyHabitCreationAddTitleCommandData(): string
    {
        $data = self::getCommonData();

        return json_encode($data);
    }

    private static function getCommonData(): array
    {
        return [
            'update_id' => 1,
            'message' => [
                'message_id' => 1,
                'from' => [
                    'id' => 1,
                    'is_bot' => false,
                    'first_name' => 'John',
                    'username' => 'johndoe',
                    'language_code' => 'ru',
                ],
                'chat' => [
                    'id' => 1,
                    'first_name' => 'John',
                    'username' => 'johndoe',
                    'type' => 'private',
                ],
                'date' => 1617554366,
            ],
        ];
    }
}
