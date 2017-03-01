<?php

namespace AppBundle\Form;

use AppBundle\Entity\Operation;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImportType extends AbstractType
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var EntityManager
     */
    private $manager;

    protected $cvs;

    public function __construct(Session $session, EntityManager $manager)
    {
        $this->session = $session;
        $this->manager = $manager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'file',
                FileType::class,
                [
                    'label' => 'form.file.label',
                    'required' => true,
                ]
            )
            ->add(
                'submit',
                SubmitType::class,
                [
                    'label' => 'form.submit.label'
                ]
            );

        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'submit']);
    }

    public function submit(FormEvent $event)
    {
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $event->getForm()->get('file')->getData();

        $handle = fopen($uploadedFile->getRealPath(), 'r');

        $first = true;
        while (false !== $data = fgetcsv($handle)) {

            if(true === $first) {
                $first = false;
                continue;
            }

            $this->cvs[] = $data;

            $operation = new Operation();
            $operation->setDate(new \DateTime($data[1]));
            $operation->setName($data[6]);
            $operation->setAmount($data[3]);
            $operation->setStatus(1);

            $this->manager->persist($operation);
        }

        fclose($handle);
    }
}
