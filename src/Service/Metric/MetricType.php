<?php

declare(strict_types=1);

namespace App\Service\Metric;

use Elao\Enum\AutoDiscoveredValuesTrait;
use Elao\Enum\Enum;

class MetricType extends Enum
{
    use AutoDiscoveredValuesTrait;

    public const HABIT_NOTIFICATION_SENT = 'habit_notification_sent';
    public const HABIT_DONE = 'habit_done';
    public const HABIT_REMIND_LATER = 'habit_remind_later';
}