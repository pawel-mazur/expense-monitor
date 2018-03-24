<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Import;
use AppBundle\Form\ImportType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class ImportController extends Controller
{
    /**
     * @Route("/import", name="import_index")
     * @Template()
     *
     * @param Request $request
     *
     * @return array|RedirectResponse
     */
    public function indexAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');

        $form = $this->get('form.factory')->create(ImportType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Import $import */
            $import = $form->getData();
            $em->persist($import);
            $em->flush();

            return $this->redirectToRoute('import_import', ['import' => $import->getId()]);
        }

        /**
         * @var Import[]
         */
        $imports = $em->getRepository(Import::class)->findBy(['user' => $this->getUser()], ['date' => 'DESC']);

        return [
            'form' => $form->createView(),
            'imports' => $imports,
        ];
    }

    /**
     * @Route("/import/{import}/{ignoreExisting}", name="import_import", requirements={"ignoreExisting"="ignore"})
     * @Template()
     *
     * @param Request $request
     * @param Import  $import
     * @param bool    $ignoreExisting
     *
     * @return array|RedirectResponse
     */
    public function importAction(Request $request, Import $import, $ignoreExisting = false)
    {
        $importer = $this->get('app.util.file_importer');

        $form = $this->createFormBuilder()->add('submit', SubmitType::class, ['label' => 'form.import.label', 'attr' => ['class' => 'btn btn-success form-control']])->getForm();
        $form->handleRequest($request);

        $importer->load($import, $ignoreExisting);

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
