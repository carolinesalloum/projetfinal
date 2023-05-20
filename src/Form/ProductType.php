<?php

namespace App\Form;

use App\Entity\Type;
use App\Entity\Level;
use App\Entity\Product;
use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if($options['file']==true){
            $builder

            ->add('title', TextType::class, [
                'label' => 'Nom du Support',
                'attr' => [
                    'class' => ' form-control form-label mt-4 mb-3 ',
                ]
                
            ])
            ->add('category', EntityType::class, [
                'label' => 'catégorie',
                'class' => Category::class ,
                'choice_label' => 'title',
            
                'attr' => [
                    'class' => ' form-control form-select  text-dark mt-4 mb-3',
                    
                ]
            
                ])
            ->add('type' , EntityType::class, [
                'label' => 'Type de Support',
                'class' => Type::class ,
                'choice_label' => 'title',
            
            'attr' => [
                'class' => ' form-control form-select text-dark mt-4 mb-3',
            ]  
             ])
            ->add('level', EntityType::class,[
                'label' => 'Niveau',
                'class' => Level::class ,
                'choice_label' => 'title',
                'attr' => [
                'class' => ' form-control  form-select text-dark mt-3 mb-3',
                    ]
                ])
                
            ->add('file',FileType::class,[
                'label' => 'Fichier',  
                'required' => false,
             
                
                'attr'=> [
                'onChange'=>'loadFile(event)',
                ]
            ])
             ->add('url', TextType::class,[

                'label' => 'ajouter lien youtube',  
                'required' => false,
               
             ])
        
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => [
                    'style' => 'margin-top: 5px',
                    'class' => 'btn btn-success',
                ]
                ])
        ;


        }elseif($options['link']==true){
            $builder

            ->add('title', TextType::class, [
                'label' => 'Nom du Support',
                'attr' => [
                    'class' => ' form-control form-label mt-4 mb-3 ',
                ]
                
            ])
            ->add('category', EntityType::class, [
                'label' => 'catégorie',
                'class' => Category::class ,
                'choice_label' => 'title',
            
                'attr' => [
                    'class' => ' form-control form-select  text-dark mt-4 mb-3',
                    
                ]
            
                ])
            ->add('type' , EntityType::class, [
                'label' => 'Type de Support',
                'class' => Type::class ,
                'choice_label' => 'title',
            
            'attr' => [
                'class' => ' form-control form-select text-dark mt-4 mb-3',
            ]  
             ])
            ->add('level', EntityType::class,[
                'label' => 'Niveau',
                'class' => Level::class ,
                'choice_label' => 'title',
                
              
            'attr' => [
                'class' => ' form-control  form-select text-dark mt-4 mb-3',
            ]
            ])
            ->add('editFile', FileType::class,[

                'label' => 'Modification Fichier',  
                'required' => false,
                
                'attr'=> [
                'onChange'=>'loadFile(event)']
            ])
            ->add('file',TextType::class,[
                'label' => 'fichier',  
                'required' => true
        
            ])
        

          
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => [
                    'style' => 'margin-top: 5px',
                    'class' => 'btn btn-success',
                ]
                ])
        ;


        }
       
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
            'file'=>false,
            'link'=>false
        ]);
    }
}
