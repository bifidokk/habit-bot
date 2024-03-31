<?php

declare(strict_types=1);

namespace App\Repository;

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
}
