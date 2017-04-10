<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Operation;
use AppBundle\Form\ImportType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');

        $form = $this->get('form.factory')->create(ImportType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $this->addFlash('success', $this->get('translator')->trans('flash.form.submitted'));
            $em->flush();
        }

        return [
            'operations' => $em->getRepository(Operation::class)->findAll(),
            'form' => $form->createView(),
        ];
    }
}
