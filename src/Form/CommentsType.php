<?php

namespace App\Form;

use App\Entity\Comments;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
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
                    'minlength'=> '20',
                    'maxlength'=> '300',
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
