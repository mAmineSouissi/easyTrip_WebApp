<?php

namespace App\Form;

use App\Entity\Hotels;
use App\Entity\Promotion;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class HotelsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de l\'hôtel',
                'attr' => ['class' => 'form-control', 'required' => true],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom de l\'hôtel est requis']),
                    new Assert\Length(['max' => 255, 'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères']),
                ],
            ])
            ->add('adresse', TextType::class, [
                'label' => 'Adresse',
                'attr' => ['class' => 'form-control', 'required' => true],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'L\'adresse est requise']),
                    new Assert\Length(['max' => 255, 'maxMessage' => 'L\'adresse ne peut pas dépasser {{ limit }} caractères']),
                ],
            ])
            ->add('city', TextType::class, [
                'label' => 'Ville',
                'attr' => ['class' => 'form-control', 'required' => true],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La ville est requise']),
                    new Assert\Length(['max' => 255, 'maxMessage' => 'La ville ne peut pas dépasser {{ limit }} caractères']),
                ],
            ])
            ->add('rating', NumberType::class, [
                'label' => 'Note (1-5)',
                'attr' => ['class' => 'form-control', 'min' => 1, 'max' => 5, 'required' => true],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La note est requise']),
                    new Assert\Range(['min' => 1, 'max' => 5, 'notInRangeMessage' => 'La note doit être entre {{ min }} et {{ max }}']),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => ['class' => 'form-control', 'rows' => 5, 'required' => true],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La description est requise']),
                ],
            ])
            ->add('price', NumberType::class, [
                'label' => 'Prix par nuit (€)',
                'attr' => [
                    'class' => 'form-control',
                    'type' => 'number',
                    'min' => 0.01,
                    'step' => 0.01,
                    'required' => true,
                    'pattern' => '[0-9]+(\\.[0-9]{1,2})?',
                    'title' => 'Veuillez entrer une valeur numérique (ex. 99.99)',
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le prix est requis']),
                    new Assert\GreaterThan(['value' => 0, 'message' => 'Le prix doit être supérieur à 0']),
                    new Assert\Type(['type' => 'numeric', 'message' => 'Le prix doit être une valeur numérique']),
                ],
            ])
            ->add('type_room', TextType::class, [
                'label' => 'Type de chambre',
                'attr' => ['class' => 'form-control', 'required' => true],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le type de chambre est requis']),
                    new Assert\Length(['max' => 255, 'maxMessage' => 'Le type de chambre ne peut pas dépasser {{ limit }} caractères']),
                ],
            ])
            ->add('num_room', NumberType::class, [
                'label' => 'Nombre de chambres',
                'attr' => ['class' => 'form-control', 'min' => 1, 'required' => true],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nombre de chambres est requis']),
                    new Assert\GreaterThan(['value' => 0, 'message' => 'Le nombre de chambres doit être supérieur à 0']),
                ],
            ])
            ->add('promotion', EntityType::class, [
                'label' => 'Promotion (optionnel)',
                'class' => Promotion::class,
                'choice_label' => function(Promotion $promotion) {
                    return sprintf('%s (-%d%%)', $promotion->getTitle(), $promotion->getDiscountPercentage());
                },
                'choice_attr' => function(Promotion $promotion) {
                    return ['data-discount' => $promotion->getDiscountPercentage()];
                },
                'placeholder' => 'Aucune promotion',
                'required' => false,
                'attr' => ['class' => 'form-select'],
            ])
            ->add('image', FileType::class, [
                'label' => 'Image de l\'hôtel',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-control', 'accept' => 'image/*'],
                'constraints' => [
                    new Assert\Image([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg', 'image/png'],
                        'mimeTypesMessage' => 'Veuillez télécharger une image JPEG ou PNG',
                        'maxSizeMessage' => 'L\'image ne doit pas dépasser 2MB',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Hotels::class,
        ]);
    }
}