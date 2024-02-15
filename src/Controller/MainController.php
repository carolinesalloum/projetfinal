<?php

namespace App\Controller;

use App\Repository\LevelRepository;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MainController extends AbstractController
{
   /**
    * @Route("/", name="app_index")
    */
    public function index( ProductRepository $productRepository, CategoryRepository $categoryRepository ,Levelrepository $levelRepository): Response
    {
        //Afficher les products, levels et categories dans la page d'acueille
     
        //On récupère la liste des levels
         $levels=$levelRepository->findAll();
         //On récupère la liste des products
         $products =  $productRepository->findAll();
        //On récupère la liste des Categories
        $categories = $categoryRepository->findAll();
         // on récupère le user
        $user = $this->getUser();
        return $this->render('front/index.html.twig', [
            'categories' => $categories,
            'products' => $products,
            'levels'=>$levels,
           'user' => $user 
        ]);
    }
   
    /**
    * @Route("/category/{categoryId}", name="index_category")
    */
    public function indexCategory($categoryId, CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();
      // nous recherchons la Category qui nous intéresse. Si celle-ci n'existe pas, nous retournons à l'index
        $category = $categoryRepository->findOneBy(['id' => $categoryId]);
        if (!$category) {
            return $this->redirectToRoute('app_index');
        }
        //Maintenant que nous avons notre Category, nous récupérons les Products qui lui sont associés
        $products = $category->getProducts();
            return $this->render('front/listcategory.html.twig', [
                'products' => $products,
               'category' => $category,
               'categories' => $categories,
            ]);
            
    
    }
    

    /**
    * @Route("/level/{levelId}", name="index_level")
    */
    // on affiche les produits selon les niveaux (levels )
    public function indexLevel( LevelRepository $levelRepository, $levelId,CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();
    //On récupère chaque level selon son Id
       $level=$levelRepository->find($levelId);
        //Maintenant que nous avons le level, nous récupérons les Products qui lui sont associés
        $products = $level->getProducts();
        
        return $this->render('front/listlevel.html.twig', [
            'level' => $level,
            'products' => $products,
            'categories' => $categories,
            
        ]);


    
    }

    /**
    * @Route("/methode", name="methode")
    */
    //cette méthode est pour afficher la page méthode 
    public function indexMethode(CategoryRepository $categoryRepository, ProductRepository $productRepository): Response
    { //on a besoin de chercher les catégories et les produits
        $categories=$categoryRepository->findAll();
        $products =  $productRepository->findAll();
        return $this->render('front/methode.html.twig', [
            'products' => $products,
            'categories'=>$categories
            
        ]);
    }

     /**
    * @Route("/ludique", name="ludique")
    */
    //cette méthode est pour afficher la page ludique
    public function indexLudique(CategoryRepository $categoryRepository, ProductRepository $productRepository): Response
    {
        //on a besoin de chercher les catégories et les produits
         $categories=$categoryRepository->findAll();
        $products =  $productRepository->findAll();
        return $this->render('front/ludique.html.twig', [
            'products' => $products,
            'categories'=>$categories
            
        ]);
    }

     /**
    * @Route("/legal", name="mentions")
    */
    //pour afficher la page mentions légales
    public function indexMention(): Response
    {
        
        return $this->render('front/mentions.html.twig');
    }
    
     /**
    * @Route("/qui-suis-je", name="quisuisje")
    */
    //pour afficher la page qui suis je
    public function indexqui(): Response
    {
        
        return $this->render('front/qui-suis-je.html.twig');
    }

     /**
    * @Route("/politique", name="politique")
    */
    //pour afficher la page mentions légales
    public function indexPolitique(): Response
    {
        
        return $this->render('front/politique.html.twig');
    }
    //  /**
    // * @Route("/compte-non-valide", name="nonvalide")
    // */
    // public function nonValide(): Response
    // {
    //     return $this->render('_partiels/nonValidecompte.html.twig');
    // }
    
}