<?php

declare(strict_types=1);

namespace App\Service\Habit\Exception;

class CouldNotCreateHabitCompletion extends \RuntimeException
{
    /**
     * @var string
     */
    protected $message = 'Could not create habit completion.';
}
