<?php

namespace App\Form;

use App\Entity\Option;
use App\Entity\PropertySearch;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
// use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;



class PropertySearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('minSurface', IntegerType::class, [
                'required' => false,
                'label' => false,
                'attr' => [
                    'placeholder' => 'Surface minimale'
                ]
            ])
            ->add('maxPrice', IntegerType::class, [
                'required' => false,
                'label' => false,
                'attr' => [
                    'placeholder' => 'Budget maximum'
                ]
            ])
            ->add('minPiece', IntegerType::class, [
                'required' => false,
                'label' => false,
                'attr' => [
                    'placeholder' => 'Pieces minimum'
                ]
            ])
            ->add('options', EntityType::class, [
                'required' => false,
                'label' => false,
                'class' => Option::class,
                'choice_label' => 'name',
                'multiple' => true,
                // 'expanded' => true,
                // 'attr' => [
                //     'placeholder' => 'Options'
                // ]
            ])
            ->add('distance', ChoiceType::class, [
                'choices' => [
                    '10km' => 10,
                    '50km' => 50,
                    '100km' => 100,
                    '500km' => 500,
                    '1000km' => 1000
                ],
                'data' => '10',
                'label' => false,
                'required' => false,
                // 'attr' => [
                //     'placeholder' => 'Distance'
                // ]
            ])
            ->add('lat', HiddenType::class)
            ->add('lng', HiddenType::class);
            // ->add('submit', SubmitType::class, [
            //     'label' => 'Rechercher',
            //     'attr' => [
            //         'class' => 'btn btn-info',
            //         'disabled' => true
            //     ]
            // ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PropertySearch::class,
            'method' => 'get',
            'csrf_protection' => false,
            'translation_domain' => 'forms'
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
