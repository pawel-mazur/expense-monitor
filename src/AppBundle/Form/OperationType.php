<?php

namespace AppBundle\Form;

use AppBundle\Entity\Contact;
use AppBundle\Entity\Operation;
use DateTime;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Date;

class OperationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Operation $operation */
        $operation = $options['data'];

        $builder
            ->add(
                'date',
                DateType::class,
                [
                    'label' => 'model._common.date',
                    'widget' => 'single_text',
                    'format' => 'dd.MM.yyyyy',
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
                    'query_builder' => function (EntityRepository $repository) {
                        return $repository->createQueryBuilder('contact')
                            ->orderBy('contact.name');
                    },
                    'required' => true,
                ]
            )
            ->add(
                'amount',
                NumberType::class,
                [
                    'label' => 'model.operation.amount',
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
