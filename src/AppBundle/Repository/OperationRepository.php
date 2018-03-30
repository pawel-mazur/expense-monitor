<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Contact;
use AppBundle\Entity\User;
use DateTime;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityRepository;

/**
 * Class OperationRepository.
 */
class OperationRepository extends EntityRepository
{
    /**
     * @param User $user
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getOperationsQB(User $user)
    {
        $qb = $this->createQueryBuilder('operation');

        $qb->select('operation', 'contact');

        $qb
            ->innerJoin('operation.contact', 'contact')
            ->where($qb->expr()->eq('operation.user', ':user'))
            ->setParameter(':user', $user)
            ->orderBy('operation.date');

        return $qb;
    }

    /**
     * @param User    $user
     * @param Contact $contact
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getOperationsByContactQB(User $user, Contact $contact = null)
    {
        $qb = $this->getOperationsQB($user);

        if (null !== $contact) {
            $qb->andWhere($qb->expr()->eq('contact', ':contact'));
            $qb->setParameter(':contact', $contact);
        }

        return $qb;
    }

    /**
     * @param User          $user
     * @param DateTime|null $dateFrom
     * @param DateTime|null $dateTo
     * @param Contact|null  $contact
     *
     * @return QueryBuilder
     */
    public function getOperationsSumQB(User $user, DateTime $dateFrom = null, DateTime $dateTo = null, Contact $contact = null)
    {
        $qb = $this->_em->getConnection()->createQueryBuilder();
        $qb
            ->select('SUM(operation.amount)', 'MAX(operation.amount)', 'MIN(operation.amount)', 'AVG(operation.amount)')
            ->from('operations', 'operation')
            ->where($qb->expr()->eq('operation.id_user', ':user'));

        $qb->setParameter(':user', $user->getId());

        if (null !== $dateFrom) {
            $qb
                ->andWhere($qb->expr()->gt('operation.date', ':dateFrom'))
                ->setParameter(':dateFrom', $dateFrom->format('Y-m-d'));
        }

        if (null !== $dateTo) {
            $qb
                ->andWhere($qb->expr()->lt('operation.date', ':dateTo'))
                ->setParameter(':dateTo', $dateTo->format('Y-m-d'));
        }

        if (null !== $contact) {
            $qb
                ->andWhere($qb->expr()->eq('operation.id_contact', ':contact'))
                ->setParameter(':contact', $contact->getId());
        }

        return $qb;
    }

    /**
     * @param User     $user
     * @param DateTime $dateFrom
     * @param DateTime $dateTo
     *
     * @return QueryBuilder
     */
    public function getOperationsSumExpensesQB(User $user, DateTime $dateFrom = null, DateTime $dateTo = null)
    {
        $qb = $this->getOperationsSumQB($user, $dateFrom, $dateTo);

        $qb->andWhere($qb->expr()->gt('operation.amount', 0));

        return $qb;
    }

    /**
     * @param User     $user
     * @param DateTime $dateFrom
     * @param DateTime $dateTo
     *
     * @return QueryBuilder
     */
    public function getOperationsSumIncomesQB(User $user, DateTime $dateFrom = null, DateTime $dateTo = null)
    {
        $qb = $this->getOperationsSumQB($user, $dateFrom, $dateTo);

        $qb->andWhere($qb->expr()->lt('operation.amount', 0));

        return $qb;
    }

    /**
     * @param User          $user
     * @param DateTime|null $dateFrom
     * @param DateTime|null $dateTo
     *
     * @return QueryBuilder
     */
    public function getOperationsSumGroupByDate(User $user, DateTime $dateFrom, DateTime $dateTo)
    {
        $qb = $this->getOperationsSumQB($user, $dateFrom, $dateTo);

        $qb
            ->addSelect('operation.date')
            ->orderBy('operation.date')
            ->groupBy('operation.date');

        if ($dateFrom->diff($dateTo)->y >= 1) {
            $select = $qb->getQueryPart('select');
            array_pop($select);

            $qb->select("to_char(operation.date, 'YYYY-MM') as date");
            $qb->addSelect($select);
            $qb->groupBy(1);
            $qb->orderBy(1);
        }

        return $qb;
    }

    /**
     * @param User     $user
     * @param DateTime $dateFrom
     * @param DateTime $dateTo
     *
     * @return QueryBuilder
     */
    public function getOperationsSumGroupByContactQB(User $user, DateTime $dateFrom, DateTime $dateTo)
    {
        $qb = $this->getOperationsSumGroupByDate($user, $dateFrom, $dateTo);

        $qb
            ->select('contact.id contact_id', 'contact.name contact_name', 'SUM(operation.amount) amount')
            ->innerJoin('operation', 'contacts', 'contact', $qb->expr()->eq('operation.id_contact', 'contact.id'))
            ->orderBy('amount')
            ->groupBy('contact.id')
        ;

        return $qb;
    }

    /**
     * @param User     $user
     * @param DateTime $dateFrom
     * @param DateTime $dateTo
     *
     * @return QueryBuilder
     */
    public function getOperationsSumGroupByContactExpensesQB(User $user, DateTime $dateFrom, DateTime $dateTo)
    {
        $qb = $this->getOperationsSumGroupByContactQB($user, $dateFrom, $dateTo);

        $qb->andWhere($qb->expr()->lt('operation.amount', 0));

        return $qb;
    }

    /**
     * @param User     $user
     * @param DateTime $dateFrom
     * @param DateTime $dateTo
     *
     * @return QueryBuilder
     */
    public function getOperationsSumGroupByContactIncomesQB(User $user, DateTime $dateFrom, DateTime $dateTo)
    {
        $qb = $this->getOperationsSumGroupByContactQB($user, $dateFrom, $dateTo);

        $qb->andWhere($qb->expr()->gt('operation.amount', 0));
        $qb->orderBy('amount', 'DESC');

        return $qb;
    }
}
