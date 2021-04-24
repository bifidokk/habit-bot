<?php

declare(strict_types=1);

namespace App\Service\Habit;

use Symfony\Component\Validator\Constraints as Assert;
use TgBotApi\BotApiBase\Type\MessageType;

class HabitDescriptionDto
{
    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=255)
     */
    public string $description;

    public static function fromMessage(MessageType $message): HabitDescriptionDto
    {
        $data = new HabitDescriptionDto();
        $data->description = (string) $message->text;

        return $data;
    }
}
