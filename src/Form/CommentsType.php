<?php

namespace App\Form;

use App\Entity\Comments;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class CommentsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
           
          
             ->add('content',TextareaType::class,
            [
            
                'label'=>'entrez votre avis',
                
                'attr'=> [
                    'class'=> 'form-control',
                    'style' => 'width:400px;height:300px;font-size:25px;display:flex;justify-content:center;',
                   
                ]  ,
                'constraints' => [
                    new NotBlank([
                        'message' => "Le contenu du commentaire ne peut pas être vide"
                    ]),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Le contenu du commentaire doit avoir au moins {{ limit }} caractères.',
                        
                        'max' => 500,
                        'maxMessage' => 'Le contenu du commentaire ne peut pas dépasser {{ limit }} caractères.'
                       
                    ]),
                ]
            ]) 
            ;
        
        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comments::class,
        ]);
    }
}
