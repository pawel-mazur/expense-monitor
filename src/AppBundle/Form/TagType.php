<?php

namespace AppBundle\Form;

use AppBundle\Entity\Contact;
use AppBundle\Entity\Tag;
use AppBundle\Repository\ContactRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class TagType extends AbstractType
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $this->tokenStorage->getToken()->getUser();

        $builder
            ->add(
                'name',
                TextType::class,
                [
                    'label' => 'model._common.name',
                ]
            )
            ->add(
                'contacts',
                EntityType::class,
                [
                    'label' => 'model.contact._title_plural',
                    'choice_label' => 'name',
                    'class' => Contact::class,
                    'query_builder' => function (ContactRepository $repository) use ($user) {
                        return $repository->createContactQB($user);
                    },
                    'by_reference' => false,
                    'required' => false,
                    'multiple' => true,
                ]
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Tag::class,
        ]);
    }
}
