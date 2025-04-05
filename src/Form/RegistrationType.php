<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'First Name',
            ])
            ->add('surname', TextType::class, [
                'label' => 'Last Name',
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email Address',
            ])
            ->add('phone', TelType::class, [
                'label' => 'Phone Number',
            ])
            ->add('addresse', TextType::class, [
                'label' => 'Address',
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Password',
            ])
            ->add('profilePhoto', TextType::class, [
                'label' => 'Profile Photo',
                'required' => false,
            ])
            ->add('role', ChoiceType::class, [
                'label' => 'Account Type',
                'choices' => [
                    'Client' => 'Client',
                    'Agent' => 'Agent',
                ],
                'expanded' => true,
                'multiple' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
