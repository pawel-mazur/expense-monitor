<?php

namespace AppBundle\Filter;

use AppBundle\Entity\Contact;
use AppBundle\Entity\Tag;
use AppBundle\Repository\ContactRepository;
use AppBundle\Repository\OperationRepository;
use AppBundle\Repository\TagRepository;
use Doctrine\DBAL\Connection;
use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\ChoiceFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\DateRangeFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\EntityFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\NumberRangeFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\TextFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class OperationFilterType extends AbstractType
{
    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(TokenStorage $tokenStorage, EventDispatcherInterface $eventDispatcher)
    {
        $this->tokenStorage = $tokenStorage;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                TextFilterType::class,
                [
                    'label' => 'model.operation._title',
                ]
            )
            ->add(
                'contact',
                EntityFilterType::class,
                [
                    'label' => 'model.contact._title_plural',
                    'choice_label' => 'name',
                    'class' => Contact::class,
                    'query_builder' => function (ContactRepository $repository) {
                        return $repository->createContactQB($this->tokenStorage->getToken()->getUser());
                    },
                    'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                        if (0 === count($values['value'])) {
                            return null;
                        }

                        $contacts = [];
                        foreach ($values['value'] as $value){$contacts[] = $value->getId();}

                        return $filterQuery->createCondition(
                            $filterQuery->getExpr()->in('operation.id_contact', ':contacts'),
                            [':contacts' => [$contacts, Connection::PARAM_INT_ARRAY]]
                        );
                    },
                    'placeholder' => '',
                    'required' => false,
                    'multiple' => true,
                ]
            )
            ->add(
                'tag',
                EntityFilterType::class,
                [
                    'label' => 'model.tag._title_plural',
                    'choice_label' => 'name',
                    'class' => Tag::class,
                    'query_builder' => function (TagRepository $repository) {
                        return $repository->createQB($this->tokenStorage->getToken()->getUser());
                    },
                    'apply_filter' => function(QueryInterface $query, $field, $values){
                        if (false == count($values['value'])) {
                            return null;
                        }

                        $tags = [];
                        foreach ($values['value'] as $value){$tags[] = $value->getId();}

                        return $query->createCondition(
                            $query->getExpr()->in('tag.id', ':tags'),
                            [':tags' => [$tags, Connection::PARAM_INT_ARRAY]]
                        );
                    },
                    'placeholder' => '',
                    'required' => false,
                    'multiple' => true,
                ]
            )
            ->add(
                'date',
                DateRangeFilterType::class,
                [
                    'label' => false,
                    'left_date_options' => [
                        'label' => 'filter.date_from',
                        'widget' => 'single_text',
                        'format' => 'dd.MM.yyyy',
                        'attr' => [
                            'class' => 'datepicker'
                        ]
                    ],
                    'right_date_options' => [
                        'label' => 'filter.date_to',
                        'widget' => 'single_text',
                        'format' => 'dd.MM.yyyy',
                        'attr' => [
                            'class' => 'datepicker'
                        ]
                    ]
                ]
            )
            ->add(
                'amount',
                NumberRangeFilterType::class,
                [
                    'label' => false,
                    'left_number_options' => [
                        'label' => 'filter.amount_from',
                        'condition_operator' => FilterOperands::OPERATOR_GREATER_THAN_EQUAL,
                    ],
                    'right_number_options' => [
                        'label' => 'filter.amount_to',
                        'condition_operator' => FilterOperands::OPERATOR_LOWER_THAN_EQUAL,
                    ]
                ]
            )
            ->add(
                'group',
                ChoiceFilterType::class,
                [
                    'label' => 'group._title',
                    'apply_filter' => false,
                    'data' => OperationRepository::GROUP_DAILY,
                    'required' => true,
                    'choices' => [
                        'group.daily' => 'daily',
                        'group.weekly' => 'weekly',
                        'group.monthly' => 'monthly',
                        'group.yearly' => 'yearly',
                    ]

                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'method' => 'GET',
        ]);
    }
}
