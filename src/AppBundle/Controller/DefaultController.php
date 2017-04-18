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
        $user = $this->getUser();

        $em = $this->get('doctrine.orm.entity_manager');

        $form = $this->get('form.factory')->create(ImportType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            try {
                $this->get('app.util.file_importer')->import($form->get('file')->getData());
                $this->addFlash('success', $this->get('translator')->trans('flash.form.success'));
                $em->flush();
            } catch (\Exception $exception) {
                $this->addFlash('danger', $this->get('translator')->trans('flash.form.error'));
            }
        }

        return [
            'operations' => $em->getRepository(Operation::class)->findAll(),
            'form' => $form->createView(),
        ];
    }
}
