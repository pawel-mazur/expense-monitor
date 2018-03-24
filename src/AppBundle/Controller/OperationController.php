<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Contact;
use AppBundle\Entity\Operation;
use AppBundle\Form\OperationType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Operation controller.
 *
 * @Route("operation")
 */
class OperationController extends Controller
{
    /**
     * Lists all operation entities.
     *
     * @Route("/{contact}", name="operation_index", requirements={"contact"="\d+"})
     * @Method("GET")
     * @Template()
     *
     * @param Contact|null $contact
     *
     * @return array
     */
    public function indexAction(Contact $contact = null)
    {
        $em = $this->getDoctrine()->getManager();
        $operationRepository = $em->getRepository(Operation::class);

        $operations = $operationRepository
            ->getOperationsByContactQB($this->getUser(), $contact)
            ->orderBy('operation.date', 'DESC')
            ->getQuery()
            ->getResult();

        $statistics = $operationRepository->getOperationsSumQB($this->getUser(), null, null, $contact)->execute()->fetch();

        return [
            'operations' => $operations,
            'statistics' => $statistics,
        ];
    }

    /**
     * Creates a new operation entity.
     *
     * @Route("/new", name="operation_new")
     * @Method({"GET", "POST"})
     * @Template()
     *
     * @param Request $request
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function newAction(Request $request)
    {
        $form = $this->createForm(OperationType::class, new Operation(), ['user' => $this->getUser()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($form->getData());
            $em->flush();

            return $this->redirectToRoute('operation_edit', ['operation' => $form->getData()->getId()]);
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * Displays a form to edit an existing operation entity.
     *
     * @Route("/{operation}/edit", name="operation_edit")
     * @Method({"GET", "POST"})
     * @Template()
     *
     * @param Request   $request
     * @param Operation $operation
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editAction(Request $request, Operation $operation)
    {
        $deleteForm = $this->createDeleteForm($operation);
        $editForm = $this->createForm(OperationType::class, $operation, ['user' => $this->getUser()]);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($editForm->getData());
            $em->flush();

            $this->addFlash('success', $this->get('translator')->trans('form.edit.flash'));

            return $this->redirectToRoute('operation_edit', ['operation' => $operation->getId()]);
        }

        return [
            'delete_form' => $deleteForm->createView(),
            'edit_form' => $editForm->createView(),
        ];
    }

    /**
     * Deletes a operation entity.
     *
     * @Route("/{operation}", name="operation_delete")
     * @Method("DELETE")
     *
     * @param Request   $request
     * @param Operation $operation
     *
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, Operation $operation)
    {
        $form = $this->createDeleteForm($operation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($operation);
            $em->flush();
        }

        return $this->redirectToRoute('operation_index');
    }

    /**
     * Creates a form to delete a operation entity.
     *
     * @param Operation $operation The operation entity
     *
     * @return Form The form
     */
    private function createDeleteForm(Operation $operation)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('operation_delete', ['operation' => $operation->getId()]))
            ->setMethod(Request::METHOD_DELETE)
            ->getForm()
        ;
    }
}
