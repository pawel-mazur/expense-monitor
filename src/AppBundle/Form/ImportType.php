<?php

namespace AppBundle\Form;

use AppBundle\Entity\Import;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class ImportType extends AbstractType
{
    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * @var string
     */
    private $importDir;

    /**
     * ImportType constructor.
     *
     * @param TokenStorage $tokenStorage
     * @param string       $importDir
     */
    public function __construct(TokenStorage $tokenStorage, $importDir)
    {
        $this->tokenStorage = $tokenStorage;
        $this->importDir = $importDir;
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
                    'label' => 'form.submit.label',
                ]
            );

        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'postSubmit']);
    }

    public function postSubmit(FormEvent $event)
    {
        /** @var Import $import */
        $import = $event->getData();

        $import->setUser($this->tokenStorage->getToken()->getUser());
        $import->setDate(new \DateTime());

        $fileName = md5(uniqid());
        $import->getFile()->move($this->importDir, $fileName);
        $import->setFileName($fileName);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Import::class,
        ]);
    }
}
