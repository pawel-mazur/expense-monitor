<?php

namespace AppBundle\Form;

use AppBundle\Entity\Operation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OperationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'date',
                DateType::class,
                [
                    'label' => 'model.operation.date',
                    'widget' => 'single_text',
                    'format' => 'yyyy.MM.dd',
                    'attr' => [
                        'class' => 'form-control input-inline datepicker',
                        'data-provide' => 'datepicker',
                        'data-date-autoclose' => true,
                        'data-date-format' => 'yyyy.mm.dd',
                        'data-date-language' => 'pl',
                    ],
                    'required' => true,
                ]
            )
            ->add(
                'name',
                null,
                [
                    'label' => 'model.operation.name',
                    'required' => true,
                ]
            )
            ->add(
                'amount',
                null,
                [
                    'label' => 'model.operation.amount',
                    'required' => true,
                ]
            );

        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'postSubmit']);
    }

    public function postSubmit(FormEvent $event)
    {
        /** @var Operation $operation */
        $operation = $event->getForm()->getData();

        $operation->setUser($event->getForm()->getConfig()->getOption('user'));
        $operation->setStatus(1);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Operation::class,
        ));

        $resolver->setRequired('user');
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_operation';
    }
}
