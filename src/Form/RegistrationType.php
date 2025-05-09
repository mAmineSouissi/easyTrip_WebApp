<?php

namespace App\Form;

use App\Entity\User;
use EWZ\Bundle\RecaptchaBundle\Form\Type\EWZRecaptchaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\IsTrue as RecaptchaTrue;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;


class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'First Name',
                'constraints' => [
                    new NotBlank(['message' => 'Please enter your first name']),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'First name should be at least {{ limit }} characters',
                    ]),
                ],
            ])
            ->add('surname', TextType::class, [
                'label' => 'Last Name',
                'constraints' => [
                    new NotBlank(['message' => 'Please enter your last name']),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email Address',
                'constraints' => [
                    new NotBlank(['message' => 'Please enter your email address']),
                    new Email(['message' => 'The email "{{ value }}" is not valid.']),
                ],
            ])
            ->add('phone', TelType::class, [
                'label' => 'Phone Number',
                'constraints' => [
                    new NotBlank(['message' => 'Please enter your phone number']),
                ],
            ])
            ->add('addresse', TextType::class, [
                'label' => 'Address',
                'constraints' => [
                    new NotBlank(['message' => 'Please enter your address']),
                ],
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Password',
                'mapped' => false,
                'constraints' => [
                    new NotBlank(['message' => 'Please enter a password']),
                ],
            ])
            ->add('profilePhoto', FileType::class, [
                'label' => 'Profile Photo (Image file)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image (jpeg, png, webp)',
                    ])
                ],
            ])
            ->add('role', ChoiceType::class, [
                'label' => 'Account Type',
                'choices' => [
                    'Client' => 'Client',
                    'Agent' => 'Agent',
                ],
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('recaptcha', EWZRecaptchaType::class, [
                'label' => false,
                'mapped' => false,
                'constraints' => [
                    new RecaptchaTrue([
                        'message' => 'Please confirm that you are not a robot.',
                    ]),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Register',
                'attr' => ['class' => 'btn btn-primary w-100'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
