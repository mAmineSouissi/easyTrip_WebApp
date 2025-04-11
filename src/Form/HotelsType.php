<?php

namespace App\Form;

use App\Entity\Hotels;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HotelsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('adresse')
            ->add('city')
            ->add('rating')
            ->add('description')
            ->add('price')
            ->add('type_room')
            ->add('num_room')
            ->add('image')
            ->add('promotion_id')
            ->add('agency_id')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Hotels::class,
        ]);
    }
}