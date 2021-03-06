<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    /**
     * @param string   $id
     * @param int|null $lockMode
     * @param int|null $lockVersion
     *
     * @return User|null
     */
    public function find($id, $lockMode = null, $lockVersion = null): ?User
    {
        /** @var User|null $entity */
        $entity = parent::find($id, $lockMode, $lockVersion);

        return $entity;
    }

    public function save(User $user)
    {
        $em = $this->getEntityManager();
        $em->persist($user);
        $em->flush($user);
    }
}
