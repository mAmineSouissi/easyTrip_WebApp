<?php

namespace App\Form;

use App\Entity\Tickets;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
            ->add('flightNumber', NumberType::class, [
                'label' => 'Numéro de vol',
                'required' => true,
                'attr' => ['min' => 1],
            ])
            ->add('airline', TextType::class, [
                'label' => 'Compagnie aérienne',
                'required' => true,
            ])
            ->add('departureCity', TextType::class, [
                'label' => 'Ville de départ',
                'required' => true,
            ])
            ->add('arrivalCity', TextType::class, [
                'label' => 'Ville d\'arrivée',
                'required' => true,
            ])
            ->add('departureDate', DateType::class, [
                'label' => 'Date de départ',
                'widget' => 'single_text',
                'required' => true,
                'attr' => [
                    'type' => 'date',
                    'min' => (new \DateTime())->format('Y-m-d'),
                ],
                'invalid_message' => 'Veuillez entrer une date de départ valide.',
            ])
            ->add('departureTime', TextType::class, [
                'label' => 'Heure de départ',
                'required' => true,
                'attr' => [
                    'placeholder' => 'HH:MM',
                    'pattern' => '[0-2][0-9]:[0-5][0-9]',
                ],
            ])
            ->add('arrivalDate', DateType::class, [
                'label' => 'Date d\'arrivée',
                'widget' => 'single_text',
                'required' => true,
                'attr' => [
                    'type' => 'date',
                    'min' => (new \DateTime())->format('Y-m-d'),
                ],
                'invalid_message' => 'Veuillez entrer une date d\'arrivée valide.',
            ])
            ->add('arrivalTime', TextType::class, [
                'label' => 'Heure d\'arrivée',
                'required' => true,
                'attr' => [
                    'placeholder' => 'HH:MM',
                    'pattern' => '[0-2][0-9]:[0-5][0-9]',
                ],
            ])
            ->add('ticketClass', ChoiceType::class, [
                'label' => 'Classe',
                'choices' => [
                    'Economy' => 'Economy',
                    'Business' => 'Business',
                    'First' => 'First',
                ],
                'required' => true,
            ])
            ->add('price', NumberType::class, [
                'label' => 'Prix (€)',
                'scale' => 2,
                'required' => true,
                'attr' => ['step' => '0.01', 'min' => '0.01'],
            ])
            ->add('ticketType', ChoiceType::class, [
                'label' => 'Type de ticket',
                'choices' => [
                    'One-way' => 'One-way',
                    'Round-trip' => 'Round-trip',
                ],
                'required' => true,
            ])
            ->add('imageAirline', TextType::class, [
                'label' => 'Logo compagnie',
                'required' => true,
            ])
            ->add('cityImage', TextType::class, [
                'label' => 'Image de la ville',
                'required' => true,
            ])
            ->add('agencyId', NumberType::class, [
                'label' => 'ID Agence',
                'required' => true,
                'attr' => ['min' => 1],
            ])
            ->add('promotionId', NumberType::class, [
                'label' => 'ID Promotion',
                'required' => true,
                'attr' => ['min' => 0],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tickets::class,
        ]);
    }
}