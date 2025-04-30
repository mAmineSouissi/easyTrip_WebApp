<?php

namespace App\Form;

use App\Entity\Tickets;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
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
                'attr' => ['min' => 1, 'class' => 'form-control'],
            ])
            ->add('airline', TextType::class, [
                'label' => 'Compagnie aérienne',
                'required' => true,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('departureCity', TextType::class, [
                'label' => 'Ville de départ',
                'required' => true,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('arrivalCity', TextType::class, [
                'label' => 'Ville d\'arrivée',
                'required' => true,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('departureDate', DateType::class, [
                'label' => 'Date de départ',
                'widget' => 'single_text',
                'required' => true,
                'attr' => [
                    'type' => 'date',
                    'min' => (new \DateTime())->format('Y-m-d'),
                    'class' => 'form-control',
                ],
                'invalid_message' => 'Veuillez entrer une date de départ valide.',
            ])
            ->add('departureTime', TextType::class, [
                'label' => 'Heure de départ',
                'required' => true,
                'attr' => [
                    'placeholder' => 'HH:MM',
                    'pattern' => '[0-2][0-9]:[0-5][0-9]',
                    'class' => 'form-control',
                ],
            ])
            ->add('arrivalDate', DateType::class, [
                'label' => 'Date d\'arrivée',
                'widget' => 'single_text',
                'required' => true,
                'attr' => [
                    'type' => 'date',
                    'min' => (new \DateTime())->format('Y-m-d'),
                    'class' => 'form-control',
                ],
                'invalid_message' => 'Veuillez entrer une date d\'arrivée valide.',
            ])
            ->add('arrivalTime', TextType::class, [
                'label' => 'Heure d\'arrivée',
                'required' => true,
                'attr' => [
                    'placeholder' => 'HH:MM',
                    'pattern' => '[0-2][0-9]:[0-5][0-9]',
                    'class' => 'form-control',
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
                'attr' => ['class' => 'form-select'],
            ])
            ->add('price', NumberType::class, [
                'label' => 'Prix (€)',
                'scale' => 2,
                'required' => true,
                'attr' => ['step' => '0.01', 'min' => '0.01', 'class' => 'form-control'],
            ])
            ->add('ticketType', ChoiceType::class, [
                'label' => 'Type de ticket',
                'choices' => [
                    'One-way' => 'One-way',
                    'Round-trip' => 'Round-trip',
                ],
                'required' => true,
                'attr' => ['class' => 'form-select'],
            ])
            ->add('imageAirline', FileType::class, [
                'label' => 'Logo compagnie',
                'required' => false,
                'mapped' => false,
                'attr' => ['class' => 'form-control-file', 'accept' => 'image/*'],
            ])
            ->add('cityImage', FileType::class, [
                'label' => 'Image de la ville',
                'required' => false,
                'mapped' => false,
                'attr' => ['class' => 'form-control-file', 'accept' => 'image/*'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tickets::class,
        ]);
    }
}