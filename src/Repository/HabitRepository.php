<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Habit;
use App\Entity\User;
use App\Service\Habit\HabitState;
use Doctrine\ORM\EntityRepository;

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

    public function getHabitForUserByState(User $user, HabitState $state): ?Habit
    {
        return $this->createQueryBuilder('h')
            ->select()
            ->where('h.user = :user')
            ->andWhere('h.state = :state')
            ->setParameter('user', $user)
            ->setParameter('state', $state->getValue())
            ->orderBy('h.createdAt', 'desc')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(Habit $habit): void
    {
        $em = $this->getEntityManager();
        $em->persist($habit);
        $em->flush($habit);
    }
}
