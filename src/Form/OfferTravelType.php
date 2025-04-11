<?php

namespace App\Form;

use App\Entity\Agency;
use App\Entity\OfferTravel;
use App\Entity\Promotion;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OfferTravelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('departure')
            ->add('destination')
            ->add('departure_date', null, [
                'widget' => 'single_text',
            ])
            ->add('arrival_date', null, [
                'widget' => 'single_text',
            ])
            ->add('hotel_name')
            ->add('discription')
            ->add('category')
            ->add('price')
            ->add('image')
            ->add('flight_name')
            ->add('agency', EntityType::class, [
                'class' => Agency::class,
                'choice_label' => 'name',
            ])
            ->add('promotion', EntityType::class, [
                'class' => Promotion::class,
                'choice_label' => 'title',
                'required' => false, // Car la relation est nullable
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OfferTravel::class,
        ]);
    }
}
