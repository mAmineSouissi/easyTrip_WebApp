<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Reclamation;

class ReclamationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEdit = $options['is_edit'] ?? false;

        $builder
            ->add('category', ChoiceType::class, [
                'label' => 'Catégorie',
                'choices' => [
                    'Service client' => 'Service client',
                    'Paiement' => 'Paiement',
                    'Problème technique' => 'Problème technique',
                    'Réservation' => 'Réservation',
                    'Autre' => 'Autre',
                ],
                'placeholder' => 'Choisissez une catégorie',
                'disabled' => $isEdit,
            ])
            ->add('issue', TextareaType::class, [
                'label' => 'Description du problème',
                'attr' => ['rows' => 5],
                'required' => !$isEdit,
                'disabled' => $isEdit,
                'empty_data' => '',
            ]);

        // ✅ N'ajoute "status" que si on est en édition
        if ($isEdit) {
            $builder->add('status', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'En cours' => 'En cours',
                    'Fermée' => 'Fermée',
                    'En attente' => 'En attente',
                ],
                'placeholder' => 'Choisissez un statut',
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reclamation::class,
            'is_edit' => false,
        ]);
    }
}
