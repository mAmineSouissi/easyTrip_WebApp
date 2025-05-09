<?php

namespace App\Form;

use App\Entity\Tickets;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class TicketsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('flightNumber', NumberType::class, [
                'label' => 'Numéro de vol',
                'attr' => [
                    'min' => 1,
                    'max' => 9999,
                    'placeholder' => 'Ex: 1234',
                    'title' => 'Le numéro de vol doit être un nombre entre 1 et 9999',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le numéro de vol est obligatoire']),
                    new Assert\Type([
                        'type' => 'numeric',
                        'message' => 'Le numéro de vol doit être un nombre'
                    ]),
                    new Assert\Range([
                        'min' => 1,
                        'max' => 9999,
                        'notInRangeMessage' => 'Le numéro de vol doit être compris entre {{ min }} et {{ max }}'
                    ])
                ]
            ])
            ->add('airline', TextType::class, [
                'label' => 'Compagnie aérienne',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La compagnie aérienne est obligatoire']),
                    new Assert\Length([
                        'min' => 2,
                        'max' => 50,
                        'minMessage' => 'Le nom de la compagnie doit faire au moins {{ limit }} caractères',
                        'maxMessage' => 'Le nom de la compagnie ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            ->add('departureCity', TextType::class, [
                'label' => 'Ville de départ',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La ville de départ est obligatoire']),
                    new Assert\Length([
                        'min' => 2,
                        'max' => 50,
                        'minMessage' => 'Le nom de la ville doit faire au moins {{ limit }} caractères',
                        'maxMessage' => 'Le nom de la ville ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            ->add('arrivalCity', TextType::class, [
                'label' => 'Ville d\'arrivée',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La ville d\'arrivée est obligatoire']),
                    new Assert\Length([
                        'min' => 2,
                        'max' => 50,
                        'minMessage' => 'Le nom de la ville doit faire au moins {{ limit }} caractères',
                        'maxMessage' => 'Le nom de la ville ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            ->add('departureDate', DateType::class, [
                'label' => 'Date de départ',
                'widget' => 'single_text',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La date de départ est obligatoire']),
                    new Assert\GreaterThanOrEqual([
                        'value' => 'today',
                        'message' => 'La date de départ doit être aujourd\'hui ou une date future'
                    ])
                ]
            ])
            ->add('departureTime', TextType::class, [
                'label' => 'Heure de départ',
                'attr' => [
                    'placeholder' => 'HH:MM',
                    'pattern' => '^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$',
                    'title' => 'Format attendu : HH:MM (ex: 14:30)',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'L\'heure de départ est obligatoire']),
                    new Assert\Regex([
                        'pattern' => '/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/',
                        'message' => 'L\'heure doit être au format HH:MM (ex: 14:30)'
                    ])
                ]
            ])
            ->add('arrivalDate', DateType::class, [
                'label' => 'Date d\'arrivée',
                'widget' => 'single_text',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La date d\'arrivée est obligatoire']),
                    new Assert\GreaterThanOrEqual([
                        'propertyPath' => 'parent.all[departureDate].data',
                        'message' => 'La date d\'arrivée doit être postérieure à la date de départ'
                    ])
                ]
            ])
            ->add('arrivalTime', TextType::class, [
                'label' => 'Heure d\'arrivée',
                'attr' => [
                    'placeholder' => 'HH:MM',
                    'pattern' => '^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$',
                    'title' => 'Format attendu : HH:MM (ex: 14:30)',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'L\'heure d\'arrivée est obligatoire']),
                    new Assert\Regex([
                        'pattern' => '/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/',
                        'message' => 'L\'heure doit être au format HH:MM (ex: 14:30)'
                    ])
                ]
            ])
            ->add('ticketClass', ChoiceType::class, [
                'label' => 'Classe',
                'choices' => [
                    'Economy' => 'Economy',
                    'Business' => 'Business',
                    'First' => 'First'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La classe est obligatoire']),
                    new Assert\Choice([
                        'choices' => ['Economy', 'Business', 'First'],
                        'message' => 'Choisissez une classe valide'
                    ])
                ]
            ])
            ->add('price', NumberType::class, [
                'label' => 'Prix',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le prix est obligatoire']),
                    new Assert\Type([
                        'type' => 'numeric',
                        'message' => 'Le prix doit être un nombre'
                    ]),
                    new Assert\GreaterThan([
                        'value' => 0,
                        'message' => 'Le prix doit être supérieur à 0'
                    ])
                ]
            ])
            ->add('ticketType', ChoiceType::class, [
                'label' => 'Type de ticket',
                'choices' => [
                    'Aller simple' => 'One-way',
                    'Aller-retour' => 'Round-trip'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le type de ticket est obligatoire']),
                    new Assert\Choice([
                        'choices' => ['One-way', 'Round-trip'],
                        'message' => 'Choisissez un type de ticket valide'
                    ])
                ]
            ])
            ->add('imageAirline', FileType::class, [
                'label' => 'Logo de la compagnie',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Assert\File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif'
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG, PNG ou GIF)',
                        'maxSizeMessage' => 'L\'image ne doit pas dépasser 2Mo'
                    ])
                ]
            ])
            ->add('cityImage', FileType::class, [
                'label' => 'Image de la ville',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Assert\File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif'
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG, PNG ou GIF)',
                        'maxSizeMessage' => 'L\'image ne doit pas dépasser 2Mo'
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tickets::class,
        ]);
    }
}