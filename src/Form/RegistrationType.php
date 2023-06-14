<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add(
                'nickname',
                TextType::class,
                [
                    'required' => false,
                    'label' => 'Pseudo',
                    'attr' => [
                        'class' => 'form-register mb-3',


                    ]
                ]
            )
            ->add(
                'email',
                EmailType::class,
                [
                    'required' => false,
                    'label' => 'Email',
                    'attr' => [
                        'class' => 'form-register mb-3',


                    ]

                ]
            )
            ->add(
                'age',
                TextType::class,
                [
                    'required' => false,
                    'label' => 'Age',

                ]
            )
            ->add(
                'password',
                PasswordType::class,
                [
                    'mapped' => false,
                    'label' => 'Mot de passe',
                    'constraints' => [
                        new NotBlank([
                            'message' => "entrez un mot de pass s'il vous plaît"
                        ]),
                        new Length([
                            'min' => 6,
                            'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères',
                            // max length allowed by Symfony for security reasons
                            'max' => 4096,
                        ]),

                    ],
                ]
            )
            ->add(
                'confirmPassword',
                PasswordType::class,
                [
                    'required' => false,
                    'label' => 'Confirmation de mot de passe',

                ]
            );
    }





    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
