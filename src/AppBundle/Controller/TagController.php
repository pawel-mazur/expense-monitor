<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Tag;
use AppBundle\Filter\TagFilterType;
use AppBundle\Form\TagType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Tag controller.
 *
 * @Route("tag")
 */
class TagController extends Controller
{
    /**
     * Lists all tag entities.
     *
     * @Route("", name="tag_index")
     * @Method("GET")
     * @Template()
     *
     * @param Request $request
     * @return array
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(Tag::class);

        $tagsQB = $repository->createQB($this->getUser());

        $filter = $this->get('form.factory')->create(TagFilterType::class);
        $filter->handleRequest($request);

        if($filter->isValid()){
            $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($filter, $tagsQB);
        }

        return [
            'filter' => $filter->createView(),
            'tags' => $tagsQB->getQuery()->getResult(),
        ];
    }

    /**
     * Creates a new tag entity.
     *
     * @Route("/new", name="tag_new")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function newAction(Request $request)
    {
        $tag = new Tag();
        $form = $this->createForm(TagType::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($tag);
            $em->flush();

            $this->addFlash('success', $this->get('translator')->trans('form.new.flash'));

            return $this->redirectToRoute('tag_edit', array('tag' => $tag->getId()));
        }

        return [
            'tag' => $tag,
            'form' => $form->createView(),
        ];
    }

    /**
     * Displays a form to edit an existing tag entity.
     *
     * @Route("/{tag}/edit", name="tag_edit")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function editAction(Request $request, Tag $tag)
    {
        $deleteForm = $this->createDeleteForm($tag);
        $editForm = $this->createForm(TagType::class, $tag);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', $this->get('translator')->trans('form.edit.flash'));

            return $this->redirectToRoute('tag_edit', array('tag' => $tag->getId()));
        }

        return [
            'tag' => $tag,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * Deletes a tag entity.
     *
     * @Route("/{tag}", name="tag_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Tag $tag)
    {
        $form = $this->createDeleteForm($tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($tag);
            $em->flush();
        }

        return $this->redirectToRoute('tag_index');
    }

    /**
     * Creates a form to delete a tag entity.
     *
     * @param Tag $tag The tag entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Tag $tag)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('tag_delete', array('tag' => $tag->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
