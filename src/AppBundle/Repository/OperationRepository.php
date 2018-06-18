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
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getOperationsQB(User $user)
    {
        $qb = $this->createQueryBuilder('operation');

        $qb->select('operation', 'contact', 'tag');

        $qb
            ->innerJoin('operation.contact', 'contact')
            ->leftJoin('contact.tags', 'tag');

        $qb
            ->where($qb->expr()->eq('operation.user', ':user'))
            ->setParameter(':user', $user);

        $qb
            ->orderBy('operation.date')
            ->addOrderBy('tag.name');

        return $qb;
    }

    /**
     * @param User          $user
     * @param DateTime|null $dateFrom
     * @param DateTime|null $dateTo
     * @param Contact|null  $contact
     * @param Tag|null      $tag
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getOperationsByContactTagQB(User $user, DateTime $dateFrom = null, DateTime $dateTo = null, Contact $contact = null, Tag $tag = null)
    {
        $qb = $this->getOperationsQB($user);

        if (null !== $contact) {
            $qb->andWhere($qb->expr()->eq('contact', ':contact'));
            $qb->setParameter(':contact', $contact);
        }

        if (null !== $tag) {
            $qb->andWhere($qb->expr()->eq('tag.id', ':tag'));
            $qb->setParameter(':tag', $tag);
        }

        if (null !== $dateFrom) {
            $qb
                ->andWhere($qb->expr()->gte('operation.date', ':dateFrom'))
                ->setParameter(':dateFrom', $dateFrom, Type::DATE);
        }

        if (null !== $dateTo) {
            $qb
                ->andWhere($qb->expr()->lte('operation.date', ':dateTo'))
                ->setParameter(':dateTo', $dateTo, Type::DATE);
        }

        return $qb;
    }

    /**
     * @param User          $user
     * @param DateTime|null $dateFrom
     * @param DateTime|null $dateTo
     * @param Contact|null  $contact
     * @param Tag|null      $tag
     *
     * @return QueryBuilder
     */
    public function getOperationsSumQB(User $user, DateTime $dateFrom = null, DateTime $dateTo = null, Contact $contact = null, Tag $tag = null)
    {
        $qb = $this->_em->getConnection()->createQueryBuilder();
        $qb
            ->select('SUM(operation.amount)', 'MAX(operation.amount)', 'MIN(operation.amount)', 'AVG(operation.amount)')
            ->from('operations', 'operation')
            ->where($qb->expr()->eq('operation.id_user', ':user'));

        $qb->setParameter(':user', $user->getId());

        if (null !== $dateFrom) {
            $qb
                ->andWhere($qb->expr()->gte('operation.date', ':dateFrom'))
                ->setParameter(':dateFrom', $dateFrom->format('Y-m-d'));
        }

        if (null !== $dateTo) {
            $qb
                ->andWhere($qb->expr()->lte('operation.date', ':dateTo'))
                ->setParameter(':dateTo', $dateTo->format('Y-m-d'));
        }

        if (null !== $contact) {
            $qb
                ->andWhere($qb->expr()->eq('operation.id_contact', ':contact'))
                ->setParameter(':contact', $contact->getId());
        }

        if (null !== $tag) {
            $qb
                ->innerJoin('operation', 'contacts', 'contact', $qb->expr()->eq('operation.id_contact', 'contact.id'))
                ->innerJoin('contact', 'contacts_tags', 'contactTag', $qb->expr()->eq('contact.id', 'contactTag.contact_id'))
                ->innerJoin('contactTag', 'tags', 'tag', $qb->expr()->eq('contactTag.tag_id', 'tag.id'))
                ->andWhere($qb->expr()->eq('tag.id', ':tag'))
                ->setParameter(':tag', $tag->getId());
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
    public function getOperationsSumIncomesQB(User $user, DateTime $dateFrom = null, DateTime $dateTo = null)
    {
        $qb = $this->getOperationsSumQB($user, $dateFrom, $dateTo);

        $qb->andWhere($qb->expr()->gt('operation.amount', 0));

        return $qb;
    }

    /**
     * @param User     $user
     * @param DateTime $dateFrom
     * @param DateTime $dateTo
     * @param string   $group
     *
     * @return QueryBuilder
     */
    public function getOperationsSumGroupByDate(User $user, DateTime $dateFrom = null, DateTime $dateTo = null, $group = self::GROUP_DAILY)
    {
        $qb = $this->getOperationsSumQB($user, $dateFrom, $dateTo);

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
     * @param DateTime|null $dateFrom
     * @param DateTime|null $dateTo
     * @param string        $group
     *
     * @return QueryBuilder
     */
    public function getOperationsSumGroupByDateExpenses(User $user, DateTime $dateFrom, DateTime $dateTo, $group)
    {
        $qb = $this->getOperationsSumGroupByDate($user, $dateFrom, $dateTo, $group);

        $qb->andWhere($qb->expr()->lt('operation.amount', 0));

        $qb->setParameter(':type', 'expenses');

        return $qb;
    }

    /**
     * @param User          $user
     * @param DateTime|null $dateFrom
     * @param DateTime|null $dateTo
     * @param string        $group
     *
     * @return QueryBuilder
     */
    public function getOperationsSumGroupByDateIncomes(User $user, DateTime $dateFrom, DateTime $dateTo, $group)
    {
        $qb = $this->getOperationsSumGroupByDate($user, $dateFrom, $dateTo, $group);

        $qb->andWhere($qb->expr()->gt('operation.amount', 0));

        $qb->setParameter(':type', 'incomes');

        return $qb;
    }

    /**
     * @param User     $user
     * @param DateTime $dateFrom
     * @param DateTime $dateTo
     * @param $group
     *
     * @return mixed
     */
    public function getOperationsTimeLineGroupByDateQB(User $user, DateTime $dateFrom, DateTime $dateTo, $group)
    {
        $sum = [
            'all' => $this->getOperationsSumGroupByDate($user, $dateFrom, $dateTo, $group)->execute()->fetchAll(PDO::FETCH_GROUP),
            'expenses' => $this->getOperationsSumGroupByDateExpenses($user, $dateFrom, $dateTo, $group)->execute()->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE),
            'incomes' => $this->getOperationsSumGroupByDateIncomes($user, $dateFrom, $dateTo, $group)->execute()->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE),
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
     * @param DateTime $dateFrom
     * @param DateTime $dateTo
     *
     * @return QueryBuilder
     */
    public function getOperationsSumGroupByContactQB(User $user, DateTime $dateFrom, DateTime $dateTo)
    {
        $qb = $this->getOperationsSumGroupByDate($user, $dateFrom, $dateTo);

        $qb
            ->select('contact.id', 'contact.name', 'SUM(operation.amount) amount')
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

    /**
     * @param User         $user
     * @param DateTime     $dateFrom
     * @param DateTime     $dateTo
     * @param Contact|null $contact
     * @param string       $group
     *
     * @return array
     */
    public function getOperationsTimeLineGroupByContactQB(User $user, DateTime $dateFrom = null, DateTime $dateTo = null, Contact $contact = null, $group = self::GROUP_DAILY)
    {
        $qb = $this->getOperationsSumGroupByDate($user, $dateFrom, $dateTo, $group);

        $qb
            ->addSelect('contact.id', 'contact.name')
            ->innerJoin('operation', 'contacts', 'contact', $qb->expr()->eq('operation.id_contact', 'contact.id'))
            ->addGroupBy('contact.id');

        if (null !== $contact) {
            $qb->andWhere($qb->expr()->eq('contact.id', ':contact'));
            $qb->setParameter(':contact', $contact->getId());
        }

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
     * @param DateTime $dateFrom
     * @param DateTime $dateTo
     *
     * @return QueryBuilder
     */
    public function getOperationsSumGroupByTagQB(User $user, DateTime $dateFrom, DateTime $dateTo)
    {
        $qb = $this->getOperationsSumGroupByDate($user, $dateFrom, $dateTo);

        $qb
            ->select('tag.id', 'tag.name', 'SUM(operation.amount) amount')
            ->innerJoin('operation', 'contacts', 'contact', $qb->expr()->eq('operation.id_contact', 'contact.id'))
            ->leftJoin('contact', 'contacts_tags', 'contactTag', $qb->expr()->eq('contact.id', 'contactTag.contact_id'))
            ->leftJoin('contactTag', 'tags', 'tag', $qb->expr()->eq('contactTag.tag_id', 'tag.id'))
            ->orderBy('amount')
            ->groupBy('tag.id')
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
    public function getOperationsSumGroupByTagExpensesQB(User $user, DateTime $dateFrom, DateTime $dateTo)
    {
        $qb = $this->getOperationsSumGroupByTagQB($user, $dateFrom, $dateTo);

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
    public function getOperationsSumGroupByTagIncomesQB(User $user, DateTime $dateFrom, DateTime $dateTo)
    {
        $qb = $this->getOperationsSumGroupByTagQB($user, $dateFrom, $dateTo);

        $qb->andWhere($qb->expr()->gt('operation.amount', 0));
        $qb->orderBy('amount', 'DESC');

        return $qb;
    }
}
