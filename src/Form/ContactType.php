<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email',EmailType::class,
            [
                'label'=> "Votre Email",
            'attr'=> [
                'class'=> 'form-control mb-3',
                
            ]
            ])
            ->add('content',TextareaType::class,
            [
                'label'=> "Votre Message",
                'attr'=> [
                    'class'=> 'form-control',
                    'style' => 'width:300px;height:300px;font-size:25px;',
                    'minlength'=> '100',
                    'maxlength'=> '400',
                ]

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
