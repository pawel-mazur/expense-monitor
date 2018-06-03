<?php

namespace AppBundle\Repository;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * TagRepository.
 */
class TagRepository extends EntityRepository
{
    /**
     * @param User $user
     *
     * @return QueryBuilder
     */
    public function createQB(User $user)
    {
        $qb = $this->createQueryBuilder('tag');

        $qb->select('tag', 'contact');

        $qb->leftJoin('tag.contacts', 'contact');

        $qb->where($qb->expr()->eq('tag.user', ':user'));
        $qb->setParameter(':user', $user->getId());

        $qb
            ->orderBy('tag.name')
            ->addOrderBy('contact.name');

        return $qb;
    }
}
