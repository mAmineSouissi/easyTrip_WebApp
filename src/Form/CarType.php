<?php

namespace App\Form;

use App\Entity\Cars;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Validator\Constraints as Assert;

class CarType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('model', TextType::class, [
                'label' => 'Modèle',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le modèle est requis']),
                    new Assert\Length([
                        'min' => 2,
                        'max' => 50,
                        'minMessage' => 'Le modèle doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le modèle ne peut pas dépasser {{ limit }} caractères'
                    ])
                ],
                'attr' => [
                    'placeholder' => 'Ex: Renault Clio',
                    'class' => 'form-control'
                ]
            ])
            ->add('seats', IntegerType::class, [
                'label' => 'Nombre de places',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nombre de places est requis']),
                    new Assert\Range([
                        'min' => 2,
                        'max' => 8,
                        'notInRangeMessage' => 'Le nombre de places doit être entre {{ min }} et {{ max }}'
                    ])
                ],
                'attr' => [
                    'min' => 2,
                    'max' => 8,
                    'class' => 'form-control'
                ]
            ])
            ->add('location', TextType::class, [
                'label' => 'Localisation',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La localisation est requise']),
                    new Assert\Length([
                        'min' => 2,
                        'max' => 100,
                        'minMessage' => 'La localisation doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'La localisation ne peut pas dépasser {{ limit }} caractères'
                    ])
                ],
                'attr' => [
                    'placeholder' => 'Ex: Paris',
                    'class' => 'form-control'
                ]
            ])
            ->add('price_per_day', NumberType::class, [
                'label' => 'Prix par jour (€)',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le prix par jour est requis']),
                    new Assert\Positive(['message' => 'Le prix doit être positif']),
                    new Assert\Range([
                        'min' => 10,
                        'max' => 1000,
                        'notInRangeMessage' => 'Le prix par jour doit être entre {{ min }}€ et {{ max }}€'
                    ])
                ],
                'attr' => [
                    'min' => 10,
                    'max' => 1000,
                    'step' => 0.01,
                    'class' => 'form-control'
                ]
            ])
            ->add('image', UrlType::class, [
                'label' => 'URL de l\'image',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'L\'URL de l\'image est requise']),
                    new Assert\Url(['message' => 'L\'URL n\'est pas valide'])
                ],
                'attr' => [
                    'placeholder' => 'https://exemple.com/image.jpg',
                    'class' => 'form-control'
                ]
            ])
            ->add('latitude', HiddenType::class, [
                'required' => false
            ])
            ->add('longitude', HiddenType::class, [
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Cars::class,
            'attr' => [
                'novalidate' => 'novalidate'
            ]
        ]);
    }
}
