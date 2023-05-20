<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if($roles = ["ROLE_USER"]){
        $builder
         
        ->add('nickname', TextType::class,
        [
            'required'=>false,
            'label'=>'Pseudo',
            
        ])
            ->add('email', EmailType::class,
            [
                'required'=>false,
                'label'=>'Email',
                
            ]
            )
            ->add('age', TextType::class,
        [
            'required'=>false,
            'label'=>'Age',
            
        ])
            ->add('password', PasswordType::class,
            [
                'required'=>false,
                'label'=>'Mot de passe',
                'attr'=>[
                    'placeholder'=>'Saisissez un mot de passe'
                ]
            ])
            ->add('confirmPassword', PasswordType::class,
            [
                'required'=>false,
                'label'=>'Confirmation de mot de passe',
               
            ])
           
            ->add('Enregistrer', SubmitType::class)
        ;
    }
    elseif ($roles = ["ROLE_ADMIN"]){
        $builder
        ->add('username', TextType::class, [
            'label' => 'Nom de l\'utilisateur',
            
        ])
        ->add('password', RepeatedType::class, [
            'type' => PasswordType::class,
            'required' => true,
            'first_options' => ['label' => 'Mot de passe',
                
            ],
            'second_options' => ['label' => 'Confirmation du mot de passe',
                
            ],
        ])
       
        ->add('submit', SubmitType::class, [
            'label' => 'Inscription',
            
        ]);
    
    }
}

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
