<?php

namespace AppBundle\Form;

use AppBundle\Entity\Contact;
use AppBundle\Entity\Operation;
use AppBundle\Repository\ContactRepository;
use DateTime;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class OperationType extends AbstractType
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
        /** @var Operation $operation */
        $operation = $options['data'];

        $user = $this->tokenStorage->getToken()->getUser();

        $builder
            ->add(
                'date',
                DateType::class,
                [
                    'label' => 'model._common.date',
                    'widget' => 'single_text',
                    'format' => 'dd.MM.yyyy',
                    'data' => null === $operation->getDate() ? new DateTime() : $operation->getDate(),
                    'attr' => [
                        'class' => 'form-control input-inline datepicker',
                        'data-provide' => 'datepicker',
                        'data-date-autoclose' => true,
                        'data-date-format' => 'dd.mm.yyyy',
                        'data-date-language' => 'pl',
                    ],
                    'required' => true,
                ]
            )
            ->add(
                'name',
                TextType::class,
                [
                    'label' => 'model._common.name',
                    'required' => true,
                ]
            )
            ->add(
                'contact',
                EntityType::class,
                [
                    'label' => 'model.contact._title',
                    'class' => Contact::class,
                    'choice_label' => 'name',
                    'placeholder' => '',
                    'query_builder' => function (ContactRepository $repository) use ($user) {
                        return $repository->createContactQB($user);
                    },
                    'required' => true,
                ]
            )
            ->add(
                'amount',
                MoneyType::class,
                [
                    'label' => 'model.operation.amount',
                    'currency' => 'PLN',
                    'required' => true,
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Operation::class,
        ]);

        $resolver->setRequired('data');
    }
}
