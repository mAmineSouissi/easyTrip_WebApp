<?php

namespace App\Form;

use App\Entity\Agency;
use App\Entity\OfferTravel;
use App\Entity\Promotion;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;


class OfferTravelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('departure', TextType::class, [
                'label' => 'Départ',
                'attr' => [
                    'placeholder' => 'Ville de départ',
                    'minlength' => 2,
                    'maxlength' => 255,
                ],
                'constraints' => [
                    new Regex([
                        'pattern' => '/^[a-zA-Z\s\-]+$/',
                        'message' => 'Le départ ne doit contenir que des lettres, espaces et tirets'
                    ])
                ],
            ])
            ->add('destination', TextType::class, [
                'label' => 'Destination',
                'attr' => [
                    'placeholder' => 'Destination du voyage',
                    'minlength' => 2,
                    'maxlength' => 255,
                ],
                'constraints' => [
                    new Regex([
                        'pattern' => '/^[a-zA-Z\s\-]+$/',
                        'message' => 'La destination ne doit contenir que des lettres, espaces et tirets'
                    ])
                ],
            ])
            ->add('departure_date', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de départ',
                'constraints' => [
                    new GreaterThanOrEqual([
                        'value' => 'today',
                        'message' => 'La date de départ doit être aujourd\'hui ou dans le futur'
                    ])
                ],
            ])
            ->add('arrival_date', DateType::class, [
                'widget' => 'single_text',
                'label' => "Date d'arrivée",
                'constraints' => [
                    new GreaterThan([
                        'propertyPath' => 'parent.all[departure_date].data',
                        'message' => 'La date d\'arrivée doit être après la date de départ'
                    ])
                ],
            ])
            ->add('hotel_name', TextType::class, [
                'label' => "Nom de l'hôtel",
                'attr' => [
                    'placeholder' => "Nom de l'hôtel",
                    'minlength' => 2,
                    'maxlength' => 255,
                ],
            ])
            ->add('discription', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'rows' => 5, 
                    'placeholder' => "Description détaillée de l'offre",
                    'minlength' => 10,
                    'maxlength' => 2000,
                ],
            ])
            ->add('category', ChoiceType::class, [
                'choices' => [
                    'Sportive' => 'Sportive',
                    'Romantique' => 'Romantique',
                    'Religieuse' => 'Religieuse',
                    'Touristique' => 'Touristique',
                ],
                'label' => 'Catégorie',
            ])
            ->add('price', NumberType::class, [
                'label' => 'Prix (€)',
                'html5' => true,
                'attr' => [
                    'placeholder' => 'Prix du voyage',
                    'class' => 'original-price',
                    'data-original-price' => $options['original_price'] ?? null,
                    'min' => 0.01,
                    'max' => 99999.99,
                    'step' => '0.01'
                ],
                'constraints' => [
                    new LessThan([
                        'value' => 100000,
                        'message' => 'Le prix doit être inférieur à 100000'
                    ])
                ],
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Image de voyage',
                'mapped' => false,
                'required' => $options['required_image'], // Utilisez l'option ici
                'attr' => [
                    'accept' => 'image/jpeg,image/png',
                    'class' => 'form-control-file'
                ],
                'constraints' => $options['required_image'] ? [
                    new NotBlank([
                        'message' => 'Veuillez sélectionner une image',
                        'groups' => ['create']
                    ]),
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide (JPEG, PNG ou WebP)',
                        'groups' => ['create']
                    ])
                ] : []
            ])
            ->add('flight_name', TextType::class, [
                'label' => 'Nom du vol',
                'attr' => [
                    'placeholder' => 'Compagnie aérienne et numéro de vol',
                    'minlength' => 2,
                    'maxlength' => 255,
                ],
            ])
            ->add('agency', EntityType::class, [
                'class' => Agency::class,
                'choice_label' => 'name',
                'label' => 'Agence',
            ])
            ->add('promotion', EntityType::class, [
                'class' => Promotion::class,
                'choice_label' => function(Promotion $promotion) {
                    return $promotion->getTitle() . ' (-' . $promotion->getDiscountPercentage() . '%)';
                },
                'required' => false,
                'label' => 'Promotion',
                'attr' => [
                    'class' => 'promotion-select',
                ],
                'choice_attr' => function(Promotion $promotion) {
                    return ['data-discount' => $promotion->getDiscountPercentage()];
                },
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OfferTravel::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'offer_travel_form',
            'original_price' => null,
            'promotions' => [],
            'validation_groups' => ['Default'],
            'required_image' => false,
        ]);

        $resolver->setAllowedTypes('required_image', 'bool');
    }
}