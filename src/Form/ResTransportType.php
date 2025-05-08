<?php

namespace App\Form;

use App\Entity\Res_transport;
use App\Entity\Cars;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\EntityRepository;

class ResTransportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $selectedCar = $options['selected_car'];
        $isAdmin = $options['is_admin'] ?? false;

        if ($selectedCar) {
            $builder->add('car', EntityType::class, [
                'class' => Cars::class,
                'choice_label' => 'model',
                'label' => 'Voiture',
                'required' => true,
                'data' => $selectedCar,
                'disabled' => true,
                'choice_attr' => function($car) {
                    return ['data-price' => $car->getPricePerDay()];
                },
                'query_builder' => function (EntityRepository $er) use ($selectedCar) {
                    return $er->createQueryBuilder('c')
                        ->where('c = :car')
                        ->setParameter('car', $selectedCar);
                },
                'constraints' => [
                    new Assert\NotNull(['message' => 'La voiture est requise'])
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ]);
        } else {
            $builder->add('car', EntityType::class, [
                'class' => Cars::class,
                'choice_label' => 'model',
                'label' => 'Voiture',
                'required' => true,
                'constraints' => [
                    new Assert\NotNull(['message' => 'La voiture est requise'])
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ]);
        }

        $builder
            ->add('startDate', DateType::class, [
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'html5' => true,
                'label' => 'Date de début',
                'required' => true,
                'constraints' => [
                    new Assert\NotNull(['message' => 'La date de début est requise']),
                    new Assert\GreaterThanOrEqual('today', null, 'La date de début ne peut pas être dans le passé')
                ],
                'attr' => [
                    'class' => 'form-control',
                    'min' => (new \DateTime())->format('Y-m-d')
                ]
            ])
            ->add('endDate', DateType::class, [
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'html5' => true,
                'label' => 'Date de fin',
                'required' => true,
                'constraints' => [
                    new Assert\NotNull(['message' => 'La date de fin est requise']),
                    new Assert\Expression(
                        "this.getParent().get('startDate').getData() === null || value > this.getParent().get('startDate').getData()",
                        message: "La date de fin doit être postérieure à la date de début"
                    )
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ]);

        // Add status field only for admin users
        if ($isAdmin) {
            $builder->add('status', ChoiceType::class, [
                'choices' => [
                    'En attente' => 'En attente',
                    'Confirmée' => 'Confirmée',
                    'Annulée' => 'Annulée'
                ],
                'label' => 'Statut',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le statut est requis']),
                    new Assert\Choice(['choices' => ['En attente', 'Confirmée', 'Annulée'], 'message' => 'Statut invalide'])
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ]);
            // Add email input for admin
            $builder->add('admin_email', \Symfony\Component\Form\Extension\Core\Type\EmailType::class, [
                'label' => "Email de l'utilisateur (pour notification)",
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => "Saisir l'email du destinataire"
                ]
            ]);
        } else {
            // For non-admin users, add a hidden field with default value
            $builder->add('status', HiddenType::class, [
                'data' => 'En attente',
                'attr' => [
                    'class' => 'd-none'
                ]
            ]);
        }

        $builder
            ->add('totalPrice', NumberType::class, [
                'label' => 'Prix total (€)',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le prix total est requis']),
                    new Assert\Positive(['message' => 'Le prix total doit être positif'])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'readonly' => true
                ]
            ])
            ->add('latitude', HiddenType::class, [
                'required' => false
            ])
            ->add('longitude', HiddenType::class, [
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Res_transport::class,
            'selected_car' => null,
            'is_admin' => false,
            'attr' => [
                'novalidate' => 'novalidate'
            ]
        ]);

        $resolver->setAllowedTypes('selected_car', ['null', Cars::class]);
        $resolver->setAllowedTypes('is_admin', 'bool');
    }
} 