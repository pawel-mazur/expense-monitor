<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * OperationRepository.
 */
class OperationRepository extends EntityRepository
{
    public function getStatistics()
    {
        $sql = <<<SQL
        
        SELECT 
          (SELECT SUM(o.amount) FROM operations o WHERE o.amount > 0) as incomes,
          (SELECT SUM(o.amount) FROM operations o WHERE o.amount < 0) as expenses,
          (SELECT SUM(o.amount) FROM operations o) as balance;
SQL;

        return [
            $this->_em->getConnection()->fetchAssoc($sql),
        ];
    }
}
