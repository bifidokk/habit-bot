<?php

declare(strict_types=1);

namespace App\Service\Message;

enum AnimationType: string
{
    case Success = 'success';
    case Timezone = 'timezone';
    case Language = 'language';
    case NotRemoved = 'not_removed';
    case Removed = 'removed';
    case HabitDone = 'habit_done';
    case HabitBusy = 'habit_busy';
}
