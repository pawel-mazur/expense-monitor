<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Operation;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
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
     * @Route("/", name="operation_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $operations = $em->getRepository('AppBundle:Operation')->findBy(['user' => $this->getUser()], ['date' => 'DESC']);

        return $this->render('@App/Operation/index.html.twig', array(
            'operations' => $operations,
        ));
    }

    /**
     * Creates a new operation entity.
     *
     * @Route("/new", name="operation_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $operation = new Operation();
        $operation->setDate(new \DateTime());
        $form = $this->createForm('AppBundle\Form\OperationType', $operation, ['user' => $this->getUser()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $operation->setUser($this->getUser());
            $em->persist($operation);
            $em->flush();

            return $this->redirectToRoute('operation_edit', array('id' => $operation->getId()));
        }

        return $this->render('@App/Operation/new.html.twig', array(
            'operation' => $operation,
            'form' => $form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing operation entity.
     *
     * @Route("/{id}/edit", name="operation_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Operation $operation)
    {
        $deleteForm = $this->createDeleteForm($operation);
        $editForm = $this->createForm('AppBundle\Form\OperationType', $operation, ['user' => $this->getUser()]);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->addFlash('success', $this->get('translator')->trans('form.edit.flash'));
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('operation_edit', array('id' => $operation->getId()));
        }

        return $this->render('@App/Operation/edit.html.twig', array(
            'operation' => $operation,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a operation entity.
     *
     * @Route("/{id}", name="operation_delete")
     * @Method("DELETE")
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
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Operation $operation)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('operation_delete', array('id' => $operation->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
