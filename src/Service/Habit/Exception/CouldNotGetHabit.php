<?php

declare(strict_types=1);

namespace App\Service\Habit\Exception;

class CouldNotGetHabit extends \RuntimeException
{
    /**
     * @var string
     */
    protected $message = 'Could not find a habit';
}
