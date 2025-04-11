<?php

namespace App\Form;

use App\Entity\Promotion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class PromotionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Titre de la promotion'
                ],
                'label' => 'Titre'
            ])
            ->add('description', TextareaType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 5,
                    'placeholder' => 'Description détaillée de la promotion'
                ],
                'label' => 'Description'
            ])
            ->add('discount_percentage', NumberType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '10',
                    'min' => 1,
                    'max' => 100,
                    'step' => 0.5
                ],
                'label' => 'Pourcentage de réduction (%)',
                'html5' => true
            ])
            ->add('valid_until', DateType::class, [
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control datepicker',
                    'min' => (new \DateTime())->format('Y-m-d')
                ],
                'label' => 'Valide jusqu\'au'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Promotion::class,
        ]);
    }
}