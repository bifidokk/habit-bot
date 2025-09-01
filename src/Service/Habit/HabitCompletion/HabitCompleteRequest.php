<?php

declare(strict_types=1);

namespace App\Service\Habit\HabitCompletion;

use Symfony\Component\Validator\Constraints as Assert;

class HabitCompleteRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Date is required')]
        #[Assert\Date(message: 'Date must be a valid date in Y-m-d format')]
        public readonly string $date = '',
    ) {
    }

    public function getDateAsDateTimeImmutable(): \DateTimeImmutable
    {
        return \DateTimeImmutable::createFromFormat('Y-m-d', $this->date);
    }
}
