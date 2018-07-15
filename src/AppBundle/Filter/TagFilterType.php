<?php

namespace AppBundle\Filter;

use AppBundle\Entity\Contact;
use AppBundle\Entity\Tag;
use AppBundle\Repository\ContactRepository;
use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\DateFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\DateRangeFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\EntityFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\TextFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Validator\Constraints\Date;

class TagFilterType extends AbstractType
{
    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    public function __construct(TokenStorage $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                TextFilterType::class,
                [
                    'label' => 'model._common.name',
                    'condition_pattern' => FilterOperands::STRING_CONTAINS,
                ]
            )
            ->add(
                'contact',
                EntityFilterType::class,
                [
                    'label' => 'model.contact._title_plural',
                    'choice_label' => 'name',
                    'apply_filter' => function(QueryInterface $query, $field, $values){

                        if (false == count($values['value'])) {
                            return null;
                        }

                        return $query->createCondition($query->getExpr()->in('contact.id', ':contacts'), [':contacts' => $values['value']]);
                    },
                    'class' => contact::class,
                    'query_builder' => function (ContactRepository $repository) {
                        return $repository->createContactQB($this->tokenStorage->getToken()->getUser());
                    },
                    'placeholder' => '',
                    'required' => false,
                    'multiple' => true,
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
