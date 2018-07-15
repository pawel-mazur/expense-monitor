<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Operation;
use AppBundle\Filter\OperationFilterType;
use AppBundle\Repository\OperationRepository;
use DateInterval;
use DateTime;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @Template()
     *
     * @param Request $request
     *
     * @param DateTime|null $dateFrom
     * @param DateTime|null $dateTo
     * @param string $group
     *
     * @return array
     */
    public function indexAction(Request $request, DateTime $dateFrom = null, DateTime $dateTo = null, $group = OperationRepository::GROUP_DAILY)
    {
        /** @var OperationRepository $operationRepository */
        $operationRepository = $this->get('doctrine')->getRepository(Operation::class);

        $filter = $this->get('form.factory')->create(OperationFilterType::class);
        $filter->handleRequest($request);

        $operationsSumStartQB = $operationRepository->getOperationsSumQB($this->getUser());
        $operationsSumQB = $operationRepository->getOperationsSumQB($this->getUser());
        $operationsSumExpensesQB = $operationRepository->getOperationsSumExpensesQB($this->getUser());
        $operationsSumIncomesQB = $operationRepository->getOperationsSumIncomesQB($this->getUser());

        $operationsSumGroupByContactExpensesQB = $operationRepository->getOperationsSumGroupByContactExpensesQB($this->getUser());
        $operationsSumGroupByContactIncomesQB = $operationRepository->getOperationsSumGroupByContactIncomesQB($this->getUser());
        $operationsSumGroupByTagExpensesQB = $operationRepository->getOperationsSumGroupByTagExpensesQB($this->getUser());
        $operationsSumGroupByTagIncomesQB = $operationRepository->getOperationsSumGroupByTagIncomesQB($this->getUser());

        if ($filter->isValid())
        {
            $dateFrom = $filter->get('date')->get('left_date')->getData();
            $dateTo = $filter->get('date')->get('right_date')->getData();
            $group = $filter->get('group')->getData();

            $qbUpdater = $this->get('lexik_form_filter.query_builder_updater');
            $qbUpdater->addFilterConditions($filter, $operationsSumStartQB);
            $qbUpdater->addFilterConditions($filter, $operationsSumQB);
            $qbUpdater->addFilterConditions($filter, $operationsSumExpensesQB);
            $qbUpdater->addFilterConditions($filter, $operationsSumIncomesQB);

            $qbUpdater->addFilterConditions($filter, $operationsSumGroupByContactExpensesQB);
            $qbUpdater->addFilterConditions($filter, $operationsSumGroupByContactIncomesQB);
            $qbUpdater->addFilterConditions($filter, $operationsSumGroupByTagExpensesQB);
            $qbUpdater->addFilterConditions($filter, $operationsSumGroupByTagIncomesQB);
        }

        $statistics = [
            'start' => $operationsSumQB->execute()->fetchColumn(),
            'balance' => $operationsSumQB->execute()->fetchColumn(),
            'expenses' => $operationsSumExpensesQB->execute()->fetch(),
            'incomes' => $operationsSumIncomesQB->execute()->fetch(),
        ];

        $operations = [
            'contact' => [
                'expenses' => $operationsSumGroupByContactExpensesQB->execute()->fetchAll(),
                'incomes' => $operationsSumGroupByContactIncomesQB->execute()->fetchAll(),
            ],
            'tag' => [
                'expenses' => $operationsSumGroupByTagExpensesQB->execute()->fetchAll(),
                'incomes' => $operationsSumGroupByTagIncomesQB->execute()->fetchAll(),
            ],
        ];

        $timeLine = $operationRepository->getOperationsTimeLineGroupByDateQB($this->getUser(), $group);

        return [
            'filter' => $filter->createView(),
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'statistics' => $statistics,
            'operations' => $operations,
            'timeLine' => $timeLine,
        ];
    }
}
