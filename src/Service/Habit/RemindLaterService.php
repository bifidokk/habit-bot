<?php

declare(strict_types=1);

namespace App\Service\Habit;

use App\Entity\Habit;
use App\Repository\HabitRepository;
use App\Service\Redis\RedisClientInterface;

class RemindLaterService
{
    private const REMIND_LATER_PERIODS_IN_MINUTES = [5, 10, 15];
    private const REMIND_LATER_PERIOD_KEY = 'habit_remind_later_%s';

    private HabitRepository $habitRepository;
    private RedisClientInterface $redisClient;

    public function __construct(
        HabitRepository $habitRepository,
        RedisClientInterface $redisClient
    ) {
        $this->habitRepository = $habitRepository;
        $this->redisClient = $redisClient;
    }

    public function remindLater(Habit $habit, \DateTimeImmutable $currentTime): ?int
    {
        $cacheKey = sprintf(
            self::REMIND_LATER_PERIOD_KEY,
            $habit->getId() ? $habit->getId()->toRfc4122() : ''
        );

        $previousPeriod = $this->redisClient->get($cacheKey);
        $periodInMinutes = null;

        if ($previousPeriod === false) {
            $periodInMinutes = self::REMIND_LATER_PERIODS_IN_MINUTES[0];
        } else {
            $key = array_search((int) $previousPeriod, self::REMIND_LATER_PERIODS_IN_MINUTES, true);

            if ($key !== false && isset(self::REMIND_LATER_PERIODS_IN_MINUTES[$key + 1])) {
                $periodInMinutes = self::REMIND_LATER_PERIODS_IN_MINUTES[$key + 1];
            }
        }

        if ($periodInMinutes !== null) {
            $remindTime = $currentTime->modify(sprintf('+%s minutes', $periodInMinutes));

            $habit->setNextRemindAt($remindTime);
            $this->habitRepository->save($habit);

            $this->redisClient->set(
                $cacheKey,
                $periodInMinutes,
                $periodInMinutes * 2 * 60
            );
        }

        return $periodInMinutes;
    }
}
