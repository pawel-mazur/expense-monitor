<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Contact;
use AppBundle\Filter\ContactFilterType;
use AppBundle\Form\ContactType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ContactController.
 *
 * @Route("/contact")
 */
class ContactController extends Controller
{
    /**
     * Lists all contact entities.
     *
     * @Route("", name="contact_index")
     * @Method("GET")
     * @Template()
     *
     * @param Request $request
     *
     * @return array
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(Contact::class);

        $contactsQB = $repository->getContactsWihOperationsQB($this->getUser());

        $filter = $this->get('form.factory')->create(ContactFilterType::class);
        $filter->handleRequest($request);

        if($filter->isValid()){
            $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($filter, $contactsQB);
        }

        return [
            'filter' => $filter->createView(),
            'contacts' => $contactsQB->getQuery()->getResult(),
        ];
    }

    /**
     * Creates a new contact entity.
     *
     * @Route("/new", name="contact_new")
     * @Method({"GET", "POST"})
     * @Template()
     *
     * @param Request $request
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function newAction(Request $request)
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($form->getData());
            $em->flush();

            $this->addFlash('success', $this->get('translator')->trans('form.new.flash'));

            return $this->redirectToRoute('contact_edit', ['contact' => $form->getData()->getId()]);
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * Displays a form to edit an existing contact entity.
     *
     * @Route("/{contact}/edit", name="contact_edit")
     * @Method({"GET", "POST"})
     * @Template()
     *
     * @param Request $request
     * @param Contact $contact
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editAction(Request $request, Contact $contact)
    {
        $deleteForm = $this->createDeleteForm($contact);
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($form->getData());
            $em->flush();

            $this->addFlash('success', $this->get('translator')->trans('form.edit.flash'));

            return $this->redirectToRoute('contact_edit', ['contact' => $contact->getId()]);
        }

        return [
            'contact' => $contact,
            'form' => $form->createView(),
            'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * Deletes a tag entity.
     *
     * @Route("/{contact}", name="contact_delete")
     * @Method("DELETE")
     *
     * @param Request $request
     * @param Contact $contact
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Request $request, Contact $contact)
    {
        $form = $this->createDeleteForm($contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($contact);
            $em->flush();
        }

        return $this->redirectToRoute('contact_index');
    }

    /**
     * Creates a form to delete a tag entity.
     *
     * @param Contact $contact The contact entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Contact $contact)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('contact_delete', array('contact' => $contact->getId())))
            ->setMethod('DELETE')
            ->getForm()
            ;
    }
}
