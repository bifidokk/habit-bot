<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use App\Entity\Habit;
use App\Entity\User;
use App\Service\Habit\HabitColor;
use Symfony\Component\HttpFoundation\Response;

class HabitControllerTest extends ApiTestCase
{
    public function testListHabitsUnauthenticated(): void
    {
        $this->unauthenticatedRequest('GET', '/api/habits');

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $this->client->getResponse()->getStatusCode());
    }

    public function testListHabitsReturnsPublishedHabits(): void
    {
        $user = $this->createUser();
        $this->createPublishedHabit($user, 'Exercise');

        $this->authenticatedRequest('GET', '/api/habits', $user);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $data = json_decode((string) $this->client->getResponse()->getContent(), true);
        $this->assertCount(1, $data);
        $this->assertSame('Exercise', $data[0]['name']);
    }

    public function testCreateHabit(): void
    {
        $user = $this->createUser();

        $this->authenticatedRequest('POST', '/api/habits', $user, [
            'name' => 'New Habit',
            'days' => [0, 1, 2],
            'time' => '09:00',
            'color' => '#ef4444',
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $data = json_decode((string) $this->client->getResponse()->getContent(), true);
        $this->assertSame('New Habit', $data['name']);
        $this->assertSame('#ef4444', $data['color']);
    }

    public function testDeleteHabit(): void
    {
        $user = $this->createUser();
        $habit = $this->createPublishedHabit($user, 'To Delete');

        $this->authenticatedRequest('DELETE', '/api/habits/' . $habit->getId()->toRfc4122(), $user);

        $this->assertSame(Response::HTTP_NO_CONTENT, $this->client->getResponse()->getStatusCode());
    }

    public function testDeleteHabitForbidden(): void
    {
        $owner = $this->createUser(11111, 'Owner');
        $otherUser = $this->createUser(22222, 'Other');
        $habit = $this->createPublishedHabit($owner, 'Not Yours');

        $this->authenticatedRequest('DELETE', '/api/habits/' . $habit->getId()->toRfc4122(), $otherUser);

        $this->assertSame(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    public function testUpdateHabit(): void
    {
        $user = $this->createUser();
        $habit = $this->createPublishedHabit($user, 'Old Name');

        $this->authenticatedRequest('PUT', '/api/habits/' . $habit->getId()->toRfc4122(), $user, [
            'name' => 'Updated Name',
            'days' => [0, 1],
            'time' => '10:00',
            'color' => '#22c55e',
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $data = json_decode((string) $this->client->getResponse()->getContent(), true);
        $this->assertSame('Updated Name', $data['name']);
    }

    public function testUpdateHabitNotFound(): void
    {
        $user = $this->createUser();

        $this->authenticatedRequest('PUT', '/api/habits/00000000-0000-0000-0000-000000000000', $user, [
            'name' => 'Test',
            'days' => [0],
            'time' => '09:00',
        ]);

        $this->assertSame(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }

    private function createPublishedHabit(User $user, string $description): Habit
    {
        $habit = new Habit();
        $habit->setUser($user);
        $habit->setDescription($description);
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
