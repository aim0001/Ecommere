<?php

namespace App\Form;

use App\Entity\City;
use App\Entity\Commande;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('phone',null,[
            'attr'=>[
                'class'=>'form form-control'
            ]
        ])
            ->add('city', EntityType::class, [
                'class' => City::class,
                'choice_label' => 'name',
                'attr'=> [
                    'class'=>'form form-control'
                ]
                ])
            
            // ->add('createdAt', null, [
            //     'widget' => 'single_text',
            // ])
            ->add('adresse',null,[
                'attr'=>[
                    'class'=>'form form-control'
                ]
            ])             
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commande::class,
        ]);
    }
}
