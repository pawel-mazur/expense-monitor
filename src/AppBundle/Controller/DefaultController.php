<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Import;
use AppBundle\Entity\Operation;
use AppBundle\Form\ImportType;
use AppBundle\Repository\OperationRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/{dateFrom}/{dateTo}", name="homepage")
     * @Template()
     *
     * @param Request   $request
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     *
     * @return array|RedirectResponse
     */
    public function indexAction(Request $request, \DateTime $dateFrom = null, \DateTime $dateTo = null)
    {
        $em = $this->get('doctrine.orm.entity_manager');

        $form = $this->get('form.factory')->create(ImportType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Import $import */
            $import = $form->getData();
            $em->persist($import);
            $em->flush();

            return $this->redirectToRoute('import', ['id' => $import->getId()]);
        }

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
            'form' => $form->createView(),
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'dates' => $dates,
            'statistics' => $statistics,
            'summaryStart' => $summaryStart,
            'summary' => $summary,
        ];
    }

    /**
     * @Route("/import/{id}", name="import")
     * @Template()
     *
     * @param Request $request
     * @param Import  $import
     *
     * @return array|RedirectResponse
     */
    public function importAction(Request $request, Import $import)
    {
        $importer = $this->get('app.util.file_importer');

        $form = $this->createFormBuilder()->add('submit', SubmitType::class, ['label' => 'form.import.label', 'attr' => ['class' => 'btn btn-success form-control']])->getForm();
        $form->handleRequest($request);

        $importer->load($import);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $importer->import();
                $this->addFlash('success', $this->get('translator')->trans('flash.form.success'));

                return $this->redirectToRoute('homepage');
            } catch (\Exception $exception) {
                $this->addFlash('danger', $this->get('translator')->trans('flash.form.error'));
            }
        }

        return [
            'import' => $import,
            'importer' => $importer,
            'form' => $form->createView(),
        ];
    }
}
