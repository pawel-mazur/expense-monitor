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
        if ($dateFrom === null) {
            $dateFrom = new \DateTime($date->format('Y-m'));
        }

        if ($dateTo === null) {
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

        $statistics = $operationRepository->getStatistics($this->getUser(), $dateFrom, $dateTo)[0];
        $summaryStart = $operationRepository->getOperationsSumQB($this->getUser(), null, $dateFrom)->getQuery()->getSingleScalarResult();
        $summary = $operationRepository->getSummary($this->getUser(), $dateFrom, $dateTo)->getQuery()->getResult();

        return [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'dates' => $dates,
            'statistics' => $statistics,
            'summaryStart' => $summaryStart,
            'summary' => $summary,
        ];
    }
}
