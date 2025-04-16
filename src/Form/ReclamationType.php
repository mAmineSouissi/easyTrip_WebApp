<?php

namespace App\Form;

use App\Entity\Reclamation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ReclamationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $role = $options['user_role'] ?? 'user';

        // Champ "Catégorie"
        $builder->add('category', ChoiceType::class, [
            'label' => 'Catégorie',
            'choices' => [
                'Service client' => 'Service client',
                'Paiement' => 'Paiement',
                'Problème technique' => 'Problème technique',
                'Réservation' => 'Réservation',
                'Autre' => 'Autre',
            ],
            'placeholder' => 'Sélectionnez une catégorie',
            'attr' => ['class' => 'form-select'],
            'disabled' => $role === 'admin', // lecture seule pour admin
            'required' => true,
        ]);

        // Champ "Problème"
        $builder->add('issue', TextType::class, [
            'label' => 'Problème',
            'constraints' => [
                new Assert\NotBlank(['message' => 'Veuillez décrire le problème.']),
                new Assert\Length([
                    'max' => 50,
                    'maxMessage' => 'Le problème ne peut pas dépasser 50 caractères.',
                ]),
            ],
            'attr' => [
                'maxlength' => 50,
                'class' => 'form-control',
            ],
            'disabled' => $role === 'admin', // lecture seule pour admin
            'required' => true,
        ]);

        // Champ "Statut" uniquement visible pour admin
        if ($role === 'admin') {
            $builder->add('status', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'En attente' => 'En attente',
                    'En cours' => 'En cours',
                    'Fermée' => 'Fermée',
                ],
                'placeholder' => 'Choisissez un statut',
                'attr' => ['class' => 'form-select'],
                'required' => true,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reclamation::class,
            'user_role' => 'user',
        ]);
    }
}
