<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TicketSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('departureCity', TextType::class, [
                'label' => 'Ville de départ',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Paris, Lyon...',
                    'class' => 'form-control'
                ]
            ])
            ->add('arrivalCity', TextType::class, [
                'label' => 'Ville d\'arrivée',
                'required' => false,
                'attr' => [
                    'placeholder' => 'New York, Tokyo...',
                    'class' => 'form-control'
                ]
            ])
            ->add('departureDate', DateType::class, [
                'label' => 'Date de départ',
                'required' => false,
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('ticketClass', ChoiceType::class, [
                'label' => 'Classe',
                'required' => false,
                'choices' => [
                    'Economy' => 'Economy',
                    'Business' => 'Business',
                    'First' => 'First'
                ],
                'attr' => [
                    'class' => 'form-select'
                ]
            ])
            ->add('maxPrice', NumberType::class, [
                'label' => 'Prix maximum (€)',
                'required' => false,
                'attr' => [
                    'placeholder' => '500',
                    'class' => 'form-control',
                    'min' => 0
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'csrf_protection' => false
        ]);
    }
}