<?php

declare(strict_types=1);

namespace App\Service\Command;

enum CommandCallbackEnum: string
{
    case HabitForm = '/formHabit';

    case HabitDescriptionForm = '/habitDescription';

    case SetHabitDescription = '/setDescription';

    case HabitRemindDayForm = '/habitDay';

    case SetHabitRemindDay = '/setDay';

    case HabitRemindTimeForm = '/habitTime';

    case SetHabitRemindTime = '/setTime';

    case HabitPreview = '/habitPreview';

    case HabitPublish = '/habitPublish';

    case SettingsTimezoneForm = '/settingsTimezone';

    case SetTimezone = '/setTimezone';

    case SettingsLanguageForm = '/settingsLanguage';

    case SetLanguage = '/setLanguage';

    case HabitList = '/listHabit';

    case HabitRemoveConfirm = '/removeConfirm';

    case HabitRemove = '/remove';

    case HabitDone = '/done';

    case HabitBusy = '/busy';

    case ToggleAnimations = '/toggleAnimations';

    case BackToRemindDay = '/backToRemindDay';

    case CancelHabitCreation = '/cancelHabitCreation';

    case BackToRemindTime = '/backToRemindTime';

    case BackToDescription = '/backToDescription';
}
