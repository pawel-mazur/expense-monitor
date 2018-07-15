<?php

namespace AppBundle\Filter;

use AppBundle\Entity\Tag;
use AppBundle\Repository\ContactRepository;
use AppBundle\Repository\TagRepository;
use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\EntityFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\TextFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class ContactFilterType extends AbstractType
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
                'tag',
                EntityFilterType::class,
                [
                    'label' => 'model.tag._title_plural',
                    'choice_label' => 'name',
                    'apply_filter' => function(QueryInterface $query, $field, $values){

                        if (false == count($values['value'])) {
                            return null;
                        }

                        return $query->createCondition($query->getExpr()->in('tag.id', ':tags'), [':tags' => $values['value']]);
                    },
                    'query_builder' => function (TagRepository $repository) {
                        return $repository->createQB($this->tokenStorage->getToken()->getUser());
                    },
                    'class' => Tag::class,
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
