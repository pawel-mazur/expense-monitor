<?php

namespace AppBundle\Repository;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * OperationRepository.
 */
class OperationRepository extends EntityRepository
{
    /**
     * @param User      $user
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     *
     * @return QueryBuilder
     */
    public function getOperationsSumQB(User $user, \DateTime $dateFrom = null, \DateTime $dateTo = null)
    {
        $qb = $this->createQueryBuilder('operation');
        $qb
            ->select('SUM(operation.amount)')
            ->where($qb->expr()->eq('operation.user', ':user'));

        $qb->setParameter(':user', $user);

        if ($dateFrom !== null) {
            $qb
                ->andWhere($qb->expr()->gt('operation.date', ':dateFrom'))
                ->setParameter(':dateFrom', $dateFrom->format('Y-m-d'));
        }

        if ($dateTo !== null) {
            $qb
                ->andWhere($qb->expr()->lt('operation.date', ':dateTo'))
                ->setParameter(':dateTo', $dateTo->format('Y-m-d'));
        }

        return $qb;
    }

    /**
     * @param User      $user
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     *
     * @return array
     */
    public function getStatistics(User $user, \DateTime $dateFrom, \DateTime $dateTo)
    {
        $sql = <<<SQL
        
        SELECT 
          (SELECT SUM(o.amount) FROM operations o WHERE o.id_user = :user AND o.amount > 0 AND o.date > :dateFrom AND o.date < :dateTo) as incomes,
          (SELECT SUM(o.amount) FROM operations o WHERE o.id_user = :user AND o.amount < 0 AND o.date > :dateFrom AND o.date < :dateTo) as expenses,
          (SELECT SUM(o.amount) FROM operations o WHERE o.id_user = :user AND o.date > :dateFrom AND o.date < :dateTo) as balance ;
SQL;

        return [
            $this->_em->getConnection()->fetchAssoc($sql, [':user' => $user->getId(), ':dateFrom' => $dateFrom->format('Y-m-d'), ':dateTo' => $dateTo->format('Y-m-d')]),
        ];
    }

    /**
     * @param User           $user
     * @param \DateTime|null $dateFrom
     * @param \DateTime|null $dateTo
     *
     * @return QueryBuilder
     */
    public function getSummary(User $user, \DateTime $dateFrom, \DateTime $dateTo)
    {
        $qb = $this->createQueryBuilder('operation');

        $qb
            ->select('operation.date', 'SUM(operation.amount) amount')
            ->orderBy('operation.date')
            ->groupBy('operation.date')
            ->where($qb->expr()->eq('operation.user', ':user'))
            ->andWhere($qb->expr()->gt('operation.date', ':dateFrom'))
            ->andWhere($qb->expr()->lt('operation.date', ':dateTo'));

        $qb
            ->setParameter(':user', $user)
            ->setParameter(':dateFrom', $dateFrom->format('Y-m-d'))
            ->setParameter(':dateTo', $dateTo->format('Y-m-d'));

        return $qb;
    }
}
