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
use Symfony\Component\Validator\Constraints as Assert;

class OfferTravelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('departure', TextType::class, [
                'label' => 'Départ',
                'attr' => ['placeholder' => 'Ville de départ']
            ])
            ->add('destination', TextType::class, [
                'label' => 'Destination',
                'attr' => ['placeholder' => 'Destination du voyage']
            ])
            ->add('departure_date', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de départ',
            ])
            ->add('arrival_date', DateType::class, [
                'widget' => 'single_text',
                'label' => "Date d'arrivée",
            ])
            ->add('hotel_name', TextType::class, [
                'label' => "Nom de l'hôtel",
                'attr' => ['placeholder' => "Nom de l'hôtel"],
            ])
            ->add('discription', TextareaType::class, [
                'label' => 'Description',
                'attr' => ['rows' => 5, 'placeholder' => "Description détaillée de l'offre"],
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
                'attr' => ['placeholder' => 'Prix du voyage'],
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Image de voyage',
                'mapped' => false, // Changer à false car on gère manuellement l'upload
                'required' => false, // Laisser à false
                'constraints' => [
                    new Assert\Image([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg', 'image/png'],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide (JPEG ou PNG)',
                    ])
                ],
                'attr' => [
                    'accept' => 'image/*',
                    'class' => 'form-control-file'
                ]
            ])
            ->add('flight_name', TextType::class, [
                'label' => 'Nom du vol',
                'attr' => ['placeholder' => 'Compagnie aérienne et numéro de vol'],
            ])
            ->add('agency', EntityType::class, [
                'class' => Agency::class,
                'choice_label' => 'name',
                'label' => 'Agence',
            ])
            ->add('promotion', EntityType::class, [
                'class' => Promotion::class,
                'choice_label' => 'title',
                'required' => false,
                'label' => 'Promotion',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OfferTravel::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'offer_travel_form'
        ]);
    }
}