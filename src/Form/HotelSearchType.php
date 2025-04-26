<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HotelSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', SearchType::class, [
                'label' => 'Nom de l\'hôtel',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Entrez le nom'],
            ])
            ->add('city', SearchType::class, [
                'label' => 'Ville',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Entrez la ville'],
            ])
            ->add('rating', ChoiceType::class, [
                'label' => 'Note minimum',
                'choices' => [
                    '1 étoile' => 1,
                    '2 étoiles' => 2,
                    '3 étoiles' => 3,
                    '4 étoiles' => 4,
                    '5 étoiles' => 5,
                ],
                'required' => false,
                'placeholder' => 'Choisir une note',
                'attr' => ['class' => 'form-select'],
            ])
            ->add('maxPrice', NumberType::class, [
                'label' => 'Prix maximum (€)',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Prix max', 'min' => 0],
            ])
            ->add('typeRoom', SearchType::class, [
                'label' => 'Type de chambre',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Type de chambre'],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Rechercher',
                'attr' => ['class' => 'btn btn-primary'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}