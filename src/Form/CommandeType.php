<?php

namespace App\Form;

use App\Entity\City;
use App\Entity\Commande;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', null,[
                'attr'=>[
                    'class'=>'form form-control'
                ],
                'label' => 'Nom'
            ])
            ->add('lastName', null, [
                'attr'=>[
                    'class'=>'form form-control'
                ],
                'label' => 'Prénom'
            ])
            ->add('phone', null, [
                'attr'=>[
                    'class'=>'form form-control'
                ]
            ])
            ->add('city', EntityType::class, [
                'class' => City::class,
                'label' => 'Ville',
                'choice_label' => 'name',
                    'attr'=>[
                        'class' => 'form form-control'
                    ]
                
            ])
            
            // ->add('createdAt', null, [
            //     'widget' => 'single_text',
            // ])
            ->add('adresse', null, [
                'attr'=>[
                    'class'=>'form form-control'
                ]
            ])
            ->add('payOnDelivery', null, [
                'label'=>'Payer à la livraison'
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
