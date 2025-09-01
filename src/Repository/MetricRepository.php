<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Habit;
use App\Entity\Metric;
use Doctrine\ORM\EntityRepository;

class MetricRepository extends EntityRepository
{
    /**
     * @param string   $id
     * @param int|null $lockMode
     * @param int|null $lockVersion
     */
    public function find($id, $lockMode = null, $lockVersion = null): ?Metric
    {
        return parent::find($id, $lockMode, $lockVersion);
    }

    public function save(Metric $metric): void
    {
        $em = $this->getEntityManager();
        $em->persist($metric);
        $em->flush();
    }

    public function findByHabitOnDate(Habit $habit, \DateTimeImmutable $date): array
    {
        $startOfDay = $date->setTime(0, 0, 0);
        $endOfDay = $date->setTime(23, 59, 59);

        return $this->createQueryBuilder('m')
            ->where('m.metricDate >= :startOfDay')
            ->andWhere('m.metricDate <= :endOfDay')
            ->andWhere('m.habit = :habit')
            ->setParameter('startOfDay', $startOfDay)
            ->setParameter('endOfDay', $endOfDay)
            ->setParameter('habit', $habit)
            ->orderBy('m.metricDate', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
