<?php
declare(strict_types=1);

namespace App\Service\Habit\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateHabitRequest
{
    use HabitRequestTrait;

    public function __construct(
        #[Assert\NotBlank(message: 'Habit name is required')]
        #[Assert\Length(
            min: 1,
            max: 255,
            minMessage: 'Habit name must be at least {{ limit }} character long',
            maxMessage: 'Habit name cannot be longer than {{ limit }} characters'
        )]
        public readonly string $name = '',
        #[Assert\NotNull(message: 'Days are required')]
        #[Assert\Type('array', message: 'Days must be an array')]
        #[Assert\Count(
            min: 0,
            max: 6,
            minMessage: 'At least {{ limit }} day must be selected',
            maxMessage: 'Cannot select more than {{ limit }} days'
        )]
        #[Assert\All([
            new Assert\Type('integer', message: 'Each day must be an integer'),
            new Assert\Range(
                notInRangeMessage: 'Day must be between {{ min }} and {{ max }} (0=Monday, 6=Sunday)',
                min: 0,
                max: 6
            ),
        ])]
        public readonly array $days = [],
        #[Assert\NotBlank(message: 'Time is required')]
        #[Assert\Regex(
            pattern: '/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/',
            message: 'Time must be in HH:MM format (24-hour)'
        )]
        public readonly string $time = ''
    ) {
    }
}
