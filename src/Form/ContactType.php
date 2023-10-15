<?php

namespace App\Form;

use App\Entity\Contact;
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
            ->add('mail',EmailType::class,
            [
                'label'=> "Votre Email",
            'attr'=> [
                'class'=> ' mb-3',
                
            ]
            ])
            ->add('content',TextareaType::class,
            [
                'label'=> "Votre Message",
                'attr'=> [
                    

                    'minlength'=> '50',
                    'maxlength'=> '400',
                ]

            ])
            
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
        ]);
    }
}
