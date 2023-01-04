<?php

declare(strict_types=1);

namespace App\Service\Metric;

enum MetricType: string
{
    case HabitNotificationSent = 'habit_notification_sent';
    case HabitDone = 'habit_done';
    case HabitRemindLater = 'habit_remind_later';
}
