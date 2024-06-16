<?php

namespace App\Form;

use App\Entity\City;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null,[
                'required'=>false,
                'label'=>'Nom ',
                'attr'=>['class'=>'input-field', 'placeholder'=>'nom de la ville']
            ])
            ->add('shippingCost', null,[
                'label'=>'Frais ',
                'attr'=>['class'=>'input-field', 'placeholder'=>'frais de port']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => City::class,
        ]);
    }
}
