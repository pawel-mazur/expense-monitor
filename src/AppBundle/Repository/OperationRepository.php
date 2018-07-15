<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Contact;
use AppBundle\Entity\Tag;
use AppBundle\Entity\User;
use DateTime;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityRepository;
use PDO;

/**
 * Class OperationRepository.
 */
class OperationRepository extends EntityRepository
{
    const GROUP_DAILY = 'daily';
    const GROUP_WEEKLY = 'weekly';
    const GROUP_MONTHLY = 'monthly';
    const GROUP_YEARLY = 'yearly';

    /**
     * @param User $user
     *
     * @return QueryBuilder
     */
    public function getOperationsQB(User $user)
    {
        $qb = $this->_em->getConnection()->createQueryBuilder();

        $qb->select([
            'operation.id as id',
            'operation.name as name',
            'operation.date as date',
            'operation.amount as amount',
            'contact.id as contact_id',
            'contact.name as contact_name',
            'string_agg(tag.name, \', \' ORDER BY tag.name) AS tag',
        ]);

        $qb->from('operations', 'operation');

        $qb
            ->innerJoin('operation', 'contacts', 'contact', $qb->expr()->eq('operation.id_contact', 'contact.id'))
            ->leftJoin('contact', 'contacts_tags', 'contact_tag', $qb->expr()->eq('contact_tag.contact_id', 'contact.id'))
            ->leftJoin('contact_tag', 'tags', 'tag', $qb->expr()->eq('contact_tag.tag_id', 'tag.id'));

        $qb
            ->where($qb->expr()->eq('operation.id_user', ':user'))
            ->setParameter(':user', $user->getId());

        return $qb;
    }

    /**
     * @param User $user
     *
     * @return QueryBuilder
     */
    public function getOperationsSumQB(User $user)
    {
        $qb = $this->getOperationsQB($user);

        $qb->select('SUM(operation.amount)', 'MAX(operation.amount)', 'MIN(operation.amount)', 'AVG(operation.amount)');

        return $qb;
    }

    /**
     * @param User     $user
     *
     * @return QueryBuilder
     */
    public function getOperationsSumExpensesQB(User $user)
    {
        $qb = $this->getOperationsSumQB($user);

        $qb->andWhere($qb->expr()->lt('operation.amount', 0));

        return $qb;
    }

    /**
     * @param User     $user
     *
     * @return QueryBuilder
     */
    public function getOperationsSumIncomesQB(User $user)
    {
        $qb = $this->getOperationsSumQB($user);

        $qb->andWhere($qb->expr()->gt('operation.amount', 0));

        return $qb;
    }

    /**
     * @param User     $user
     * @param string   $group
     *
     * @return QueryBuilder
     */
    public function getOperationsSumGroupByDate(User $user, $group = self::GROUP_DAILY)
    {
        $qb = $this->getOperationsSumQB($user);

        $qb->select('SUM(operation.amount)', 'MAX(operation.amount)', 'MIN(operation.amount)', 'AVG(operation.amount)', ':type as type');

        $select = $qb->getQueryPart('select');

        switch ($group) {
            case self::GROUP_DAILY:

                $qb->select("to_char(operation.date, 'YYYY-MM-DD') as date");
                break;

            case self::GROUP_WEEKLY:

                $qb->select("to_char(operation.date, 'YYYY-WW') as date");
                break;

            case self::GROUP_MONTHLY:

                $qb->select("to_char(operation.date, 'YYYY-MM') as date");
                break;

            case self::GROUP_YEARLY:

                $qb->select("to_char(operation.date, 'YYYY') as date");
                break;
        }

        $qb->addSelect($select)
            ->groupBy(1)
            ->orderBy(1);

        $qb->setParameter(':type', 'all');

        return $qb;
    }

    /**
     * @param User          $user
     * @param string        $group
     *
     * @return QueryBuilder
     */
    public function getOperationsSumGroupByDateExpenses(User $user, $group)
    {
        $qb = $this->getOperationsSumGroupByDate($user, $group);

        $qb->andWhere($qb->expr()->lt('operation.amount', 0));

        $qb->setParameter(':type', 'expenses');

        return $qb;
    }

    /**
     * @param User          $user
     * @param string        $group
     *
     * @return QueryBuilder
     */
    public function getOperationsSumGroupByDateIncomes(User $user, $group)
    {
        $qb = $this->getOperationsSumGroupByDate($user, $group);

        $qb->andWhere($qb->expr()->gt('operation.amount', 0));

        $qb->setParameter(':type', 'incomes');

        return $qb;
    }

    /**
     * @param User     $user
     * @param $group
     *
     * @return mixed
     */
    public function getOperationsTimeLineGroupByDateQB(User $user, $group = self::GROUP_DAILY)
    {
        $sum = [
            'all' => $this->getOperationsSumGroupByDate($user, $group)->execute()->fetchAll(PDO::FETCH_GROUP),
            'expenses' => $this->getOperationsSumGroupByDateExpenses($user, $group)->execute()->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE),
            'incomes' => $this->getOperationsSumGroupByDateIncomes($user, $group)->execute()->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE),
        ];

        $timeLine = $sum['all'];
        foreach ($sum['expenses'] as $day => $data) {
            array_push($timeLine[$day], $data);
        }
        foreach ($sum['incomes'] as $day => $data) {
            array_push($timeLine[$day], $data);
        }

        return $timeLine;
    }

    /**
     * @param User     $user
     *
     * @return QueryBuilder
     */
    public function getOperationsSumGroupByContactQB(User $user)
    {
        $qb = $this->getOperationsSumGroupByDate($user);

        $qb
            ->select('contact.id', 'contact.name', 'SUM(operation.amount) amount')
            ->orderBy('amount')
            ->groupBy('contact.id')
            ->setMaxResults(10)
        ;

        return $qb;
    }

    /**
     * @param User     $user
     *
     * @return QueryBuilder
     */
    public function getOperationsSumGroupByContactExpensesQB(User $user)
    {
        $qb = $this->getOperationsSumGroupByContactQB($user);

        $qb->andWhere($qb->expr()->lt('operation.amount', 0));

        return $qb;
    }

    /**
     * @param User     $user
     *
     * @return QueryBuilder
     */
    public function getOperationsSumGroupByContactIncomesQB(User $user)
    {
        $qb = $this->getOperationsSumGroupByContactQB($user);

        $qb->andWhere($qb->expr()->gt('operation.amount', 0));
        $qb->orderBy('amount', 'DESC');

        return $qb;
    }

    /**
     * @param User         $user
     * @param string       $group
     *
     * @return array
     */
    public function getOperationsTimeLineGroupByContactQB(User $user, $group = self::GROUP_DAILY)
    {
        $qb = $this->getOperationsSumGroupByDate($user, $group);

        $qb
            ->addSelect('contact.id', 'contact.name')
            ->addGroupBy('contact.id');

        $data = $qb->execute()->fetchAll();
        $timeLine = [];
        $contact = [];

        foreach ($data as $row) {
            $timeLine[$row['date']][$row['id']] = $row;
            $contact[$row['id']] = $row['name'];
        }

        return ['days' => $timeLine, 'legend' => $contact];
    }

    /**
     * @param User     $user
     *
     * @return QueryBuilder
     */
    public function getOperationsSumGroupByTagQB(User $user)
    {
        $qb = $this->getOperationsSumGroupByDate($user);

        $qb
            ->select('tag.id', 'tag.name', 'SUM(operation.amount) amount')
            ->orderBy('amount')
            ->groupBy('tag.id')
            ->setMaxResults(10)
        ;

        return $qb;
    }

    /**
     * @param User     $user
     *
     * @return QueryBuilder
     */
    public function getOperationsSumGroupByTagExpensesQB(User $user)
    {
        $qb = $this->getOperationsSumGroupByTagQB($user);

        $qb->andWhere($qb->expr()->lt('operation.amount', 0));

        return $qb;
    }

    /**
     * @param User     $user
     *
     * @return QueryBuilder
     */
    public function getOperationsSumGroupByTagIncomesQB(User $user)
    {
        $qb = $this->getOperationsSumGroupByTagQB($user);

        $qb->andWhere($qb->expr()->gt('operation.amount', 0));
        $qb->orderBy('amount', 'DESC');

        return $qb;
    }
}
