<?php

declare(strict_types=1);

namespace App\Service\Habit\Exception;

class CouldNotGetHabit extends \RuntimeException
{
    /**
     * @var string $message
     */
    protected $message = 'Could not find a habit';
}
