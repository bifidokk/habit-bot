<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use App\Entity\Habit;
use App\Entity\User;
use App\Service\Habit\HabitColor;
use Symfony\Component\HttpFoundation\Response;

class HabitCompletionControllerTest extends ApiTestCase
{
    public function testCompleteHabit(): void
    {
        $user = $this->createUser();
        $habit = $this->createPublishedHabit($user);

        $this->authenticatedRequest('POST', '/api/habits/' . $habit->getId()->toRfc4122() . '/completions', $user, [
            'date' => '2026-03-07',
            'completed' => true,
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testUncompleteHabit(): void
    {
        $user = $this->createUser();
        $habit = $this->createPublishedHabit($user);

        $this->authenticatedRequest('POST', '/api/habits/' . $habit->getId()->toRfc4122() . '/completions', $user, [
            'date' => '2026-03-07',
            'completed' => false,
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testCompleteHabitUnauthenticated(): void
    {
        $this->unauthenticatedRequest('POST', '/api/habits/00000000-0000-0000-0000-000000000000/completions', [
            'date' => '2026-03-07',
            'completed' => true,
        ]);

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $this->client->getResponse()->getStatusCode());
    }

    public function testCompleteHabitForbidden(): void
    {
        $owner = $this->createUser(11111, 'Owner');
        $otherUser = $this->createUser(22222, 'Other');
        $habit = $this->createPublishedHabit($owner);

        $this->authenticatedRequest('POST', '/api/habits/' . $habit->getId()->toRfc4122() . '/completions', $otherUser, [
            'date' => '2026-03-07',
            'completed' => true,
        ]);

        $this->assertSame(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    private function createPublishedHabit(User $user): Habit
    {
        $habit = new Habit();
        $habit->setUser($user);
        $habit->setDescription('Test Habit');
        $habit->setRemindWeekDays(127);
        $habit->setRemindAt(new \DateTimeImmutable('09:00'));
        $habit->setColor(HabitColor::DEFAULT);
        $habit->publish();
        $habit->setNextRemindAt(new \DateTimeImmutable('+1 day'));

        $em = $this->getEntityManager();
        $em->persist($habit);
        $em->flush();

        return $habit;
    }
}
