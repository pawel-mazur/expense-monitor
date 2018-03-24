<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Contact;
use AppBundle\Form\ContactType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ContactController.
 *
 * @Route("/contractor")
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
     * @return array
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(Contact::class);

        $contacts = $repository->getContactsWihOperationsQB($this->getUser())
            ->getQuery()
            ->getResult();

        return [
            'contacts' => $contacts,
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
        $form = $this->createForm(ContactType::class, null, ['user' => $this->getUser()]);
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
        $form = $this->createForm(ContactType::class, $contact, ['user' => $this->getUser()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($form->getData());
            $em->flush();

            $this->addFlash('success', $this->get('translator')->trans('form.edit.flash'));

            return $this->redirectToRoute('contact_edit', ['contact' => $contact->getId()]);
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
