<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Habit;
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

    public function save(Habit $habit): void
    {
        $em = $this->getEntityManager();
        $em->persist($habit);
        $em->flush($habit);
    }
}
