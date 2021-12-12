<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Habit;
use App\Entity\User;
use App\Service\Habit\HabitState;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Uid\Uuid;

class HabitRepository extends EntityRepository
{
    /**
     * @param string   $id
     * @param int|null $lockMode
     * @param int|null $lockVersion
     */
    public function find($id, $lockMode = null, $lockVersion = null): ?Habit
    {
        return parent::find($id, $lockMode, $lockVersion);
    }

    public function removeUserHabitsWithState(User $user, HabitState $state): int
    {
        return $this->createQueryBuilder('h')
            ->delete()
            ->where('h.user = :user')
            ->andWhere('h.state = :state')
            ->setParameter('user', $user->getId())
            ->setParameter('state', $state->getValue())
            ->getQuery()
            ->execute();
    }

    public function findByIdWithState(string $id, HabitState $state): ?Habit
    {
        return $this->createQueryBuilder('h')
            ->where('h.id = :id')
            ->andWhere('h.state = :state')
            ->setParameter('id', Uuid::fromString($id))
            ->setParameter('state', $state->getValue())
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findById(string $id): ?Habit
    {
        return $this->createQueryBuilder('h')
            ->where('h.id = :id')
            ->setParameter('id', Uuid::fromString($id))
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findReadyForRemindHabits(): array
    {
        $currentTime = new \DateTimeImmutable();

        return $this->createQueryBuilder('h')
            ->select('h, u')
            ->where('h.nextRemindAt is not null')
            ->andWhere('h.nextRemindAt <= :currentTime')
            ->setParameter('currentTime', $currentTime)
            ->leftJoin('h.user', 'u')
            ->getQuery()
            ->getResult();
    }

    public function findByUser(User $user): array
    {
        $state = HabitState::get(HabitState::PUBLISHED);

        return $this->createQueryBuilder('h')
            ->select('h')
            ->where('h.user = :user')
            ->andWhere('h.state = :state')
            ->setParameter('user', $user->getId())
            ->setParameter('state', $state->getValue())
            ->getQuery()
            ->getResult();
    }

    public function delete(Habit $habit): int
    {
        return $this->createQueryBuilder('h')
            ->delete()
            ->where('h.id = :id')
            ->setParameter('id', $habit->getId())
            ->getQuery()
            ->execute();
    }

    public function save(Habit $habit): void
    {
        $em = $this->getEntityManager();
        $em->persist($habit);
        $em->flush($habit);
    }
}
