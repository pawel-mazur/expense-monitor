<?php

namespace AppBundle\Repository;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ContactRepository.
 */
class ContactRepository extends EntityRepository
{
    /**
     * @param User $user
     *
     * @return QueryBuilder
     */
    public function createContactQB(User $user)
    {
        $qb = $this->createQueryBuilder('contact');

        $qb->where($qb->expr()->eq('contact.user', ':user'));
        $qb->setParameter(':user', $user->getId());

        $qb->orderBy('contact.name');

        return $qb;
    }

    /**
     * @param User $user
     *
     * @return QueryBuilder
     */
    public function getContactsWihOperationsQB(User $user)
    {
        $qb = $this->createContactQB($user);

        $qb->select('contact', 'operations');

        $qb->leftJoin('contact.operations', 'operations');

        return $qb;
    }
}
