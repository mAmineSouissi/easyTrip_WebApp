<?php

namespace App\Form;

use App\Entity\Tickets;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TicketsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('flightNumber', NumberType::class)
            ->add('airline', TextType::class)
            ->add('departureCity', TextType::class)
            ->add('arrivalCity', TextType::class)
            ->add('departureDate', DateType::class, [
                'widget' => 'single_text', // Cette option est valide pour DateType
                'html5' => true
            ])
            ->add('departureTime', TextType::class)
            ->add('arrivalDate', DateType::class, [
                'widget' => 'single_text', // Cette option est valide pour DateType
                'html5' => true
            ])
            ->add('arrivalTime', TextType::class)
            ->add('ticketClass', TextType::class)
            ->add('price', NumberType::class)
            ->add('ticketType', TextType::class)
            ->add('imageAirline', TextType::class)
            ->add('cityImage', TextType::class)
            ->add('agencyId', NumberType::class)
            ->add('promotionId', NumberType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tickets::class,
        ]);
    }
}