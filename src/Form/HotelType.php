<?php

namespace App\Form;

use App\Entity\Hotel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class HotelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de l\'hôtel',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom de l\'hôtel est obligatoire']),
                    new Assert\Length([
                        'min' => 2,
                        'max' => 100,
                        'minMessage' => 'Le nom doit faire au moins {{ limit }} caractères',
                        'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            ->add('location', TextType::class, [
                'label' => 'Localisation',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La localisation est obligatoire']),
                    new Assert\Length([
                        'min' => 2,
                        'max' => 100,
                        'minMessage' => 'La localisation doit faire au moins {{ limit }} caractères',
                        'maxMessage' => 'La localisation ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            ->add('stars', ChoiceType::class, [
                'label' => 'Nombre d\'étoiles',
                'choices' => [
                    '1 étoile' => 1,
                    '2 étoiles' => 2,
                    '3 étoiles' => 3,
                    '4 étoiles' => 4,
                    '5 étoiles' => 5
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nombre d\'étoiles est obligatoire']),
                    new Assert\Choice([
                        'choices' => [1, 2, 3, 4, 5],
                        'message' => 'Choisissez un nombre d\'étoiles valide'
                    ])
                ]
            ])
            ->add('price', NumberType::class, [
                'label' => 'Prix par nuit',
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
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'constraints' => [
                    new Assert\Length([
                        'max' => 1000,
                        'maxMessage' => 'La description ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            ->add('image', FileType::class, [
                'label' => 'Image de l\'hôtel',
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
            ->add('amenities', TextType::class, [
                'label' => 'Équipements',
                'required' => false,
                'constraints' => [
                    new Assert\Length([
                        'max' => 500,
                        'maxMessage' => 'La liste des équipements ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Hotel::class,
        ]);
    }
} 