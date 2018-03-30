<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Operation;
use AppBundle\Repository\OperationRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DefaultController extends Controller
{
    /**
     * @Route("/{dateFrom}/{dateTo}", name="homepage")
     * @Template()
     *
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     *
     * @return array|RedirectResponse
     */
    public function indexAction(\DateTime $dateFrom = null, \DateTime $dateTo = null)
    {
        /** @var OperationRepository $operationRepository */
        $operationRepository = $this->get('doctrine.orm.entity_manager')->getRepository(Operation::class);

        $date = new \DateTime();
        if (null === $dateFrom) {
            $dateFrom = new \DateTime($date->format('Y-m'));
        }

        if (null === $dateTo) {
            $dateTo = clone $dateFrom;
            $dateTo->add(new \DateInterval('P1M'));
        }

        $datePrevFrom = clone $dateFrom;
        $datePrevTo = clone $dateTo;
        $dateNextFrom = clone $dateFrom;
        $dateNextTo = clone $dateTo;

        $interval = date_diff($dateFrom, $dateTo);
        $dates = [
            'prevFrom' => $datePrevFrom->sub($interval),
            'prevTo' => $datePrevTo->sub($interval),
            'nextFrom' => $dateNextFrom->add($interval),
            'nextTo' => $dateNextTo->add($interval),
        ];

        $statistics = [
            'start' => $operationRepository->getOperationsSumQB($this->getUser(), null, $dateFrom)->execute()->fetchColumn(),
            'balance' => $operationRepository->getOperationsSumQB($this->getUser(), $dateFrom, $dateTo)->execute()->fetchColumn(),
            'expenses' => $operationRepository->getOperationsSumExpensesQB($this->getUser(), $dateFrom, $dateTo)->execute()->fetch(),
            'incomes' => $operationRepository->getOperationsSumIncomesQB($this->getUser(), $dateFrom, $dateTo)->execute()->fetch(),
        ];

        $operations = [
            'all' => $operationRepository->getOperationsSumGroupByDate($this->getUser(), $dateFrom, $dateTo)->execute()->fetchAll(),
            'expenses' => $operationRepository->getOperationsSumGroupByContactExpensesQB($this->getUser(), $dateFrom, $dateTo)->execute()->fetchAll(),
            'incomes' => $operationRepository->getOperationsSumGroupByContactIncomesQB($this->getUser(), $dateFrom, $dateTo)->execute()->fetchAll(),
        ];

        return [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'dates' => $dates,
            'statistics' => $statistics,
            'operations' => $operations,
        ];
    }
}
