<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

//Activez la protection CSRF dans les formulaires pour prévenir les attaques CSRF.
        
            ->add(
                'nickname',
                TextType::class,
                [
                    'required' => true,
                    'label' => 'Pseudo',
                    'attr' => [
                        'class' => 'form-register mb-3',


                    ],
                    'constraints' => [
                        new Length([
                            'min' => 3,
                            'max' => 15,
                            'minMessage' => 'Le pseudo doit comporter au moins {{ limit }} caractères',
                            'maxMessage' => 'Le pseudo ne peut pas comporter plus de {{ limit }} caractères',
                        ]),
                    ],
                ]
            )
           
            ->add(
                'email',
                EmailType::class,
                [
                    'required' => true,
                    'label' => 'Email',
                    'attr' => [
                        'class' => 'form-register mb-3',


                    ]

                ]
            )
            ->add(
                'age',
                IntegerType::class,
                [
                    'required' => false,
                    'label' => 'Age',

                ]
            )
           
        ->add('password', RepeatedType::class, [
            'type' => PasswordType::class,
                'first_options'  => ['label' => 'Password'],
                'second_options' => ['label' => 'Confirm Password'],
                'constraints' => [
                    new NotBlank([
                        'message' => "entrez un mot de pass s'il vous plaît"
                    ]),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                       
                    ]),
                //     new Regex([
                //         'pattern' => " /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/ " ,
                //           'message' => 'Le mot de passe doit contenir au moins une minuscule, une majuscule, un chiffre et un caractère spécial'
                // ]),

                ],
                // 'invalid_message' => 'les deux mots de passe ne corresponde pas',
            ])
            
            
            
            ->add('acceptedTerms',CheckboxType::class,
            [
                'mapped' => false,
                'required' => false,
                'label_html' => true,
                'label_attr' => [
                    'class'=> 'mb-3',

            ],
                'label'=> 'J\'accepte les <a href="/legal#legal"> conditions générales d\'utilisation </a>',
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter les conditions générales pour vous inscrire.',
                    ]),
                ],
                
               
            ])
            ->add('agreepolitique',CheckboxType::class,
            [
                'mapped' => false,
                'required' => false,
                'label_html' => true,
                'label_attr' => [
                    'class'=> 'mb-3',

            ],
                'label'=> 'J\'accepte la <a href="/politique#politique"> politique de protection des données </a>',
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter la politique de protection des données pour vous inscrire.',
                    ]),
                ],
                
               
            ])
        
            
            ;
    }





    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
