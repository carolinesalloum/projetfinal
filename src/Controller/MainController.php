<?php

namespace App\Controller;

use App\Repository\LevelRepository;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use App\Repository\CommentsRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MainController extends AbstractController
{
   /**
    * @Route("/", name="app_index")
    */
    public function index( ProductRepository $productRepository, CategoryRepository $categoryRepository ,Levelrepository $levelRepository, CommentsRepository $commentsRepository): Response
    {
        //Afficher les products, levels et categories dans la page d'acueille
      $user = $this->getUser();
        //On récupère la liste des levels
         $levels=$levelRepository->findAll();
         //On récupère la liste des products
         $products =  $productRepository->findAll();
        //On récupère la liste des Categories
        $categories = $categoryRepository->findAll();
    $comments = $commentsRepository->findAll();
        return $this->render('front/index.html.twig', [
            'categories' => $categories,
            'products' => $products,
            'levels'=>$levels,
        'comments' => $comments,
           'user' => $user 
        ]);
    }
   
    /**
    * @Route("/category/{categoryId}", name="index_category")
    */
    public function indexCategory($categoryId, CategoryRepository $categoryRepository): Response
    {
        
        //On récupère la liste des Categories
        $categories = $categoryRepository->findAll();
      
      // nous recherchons la Category qui nous intéresse. Si celle-ci n'existe pas, nous retournons à l'index
        $category = $categoryRepository->findOneBy(['id' => $categoryId]);
        if (!$category) {
            return $this->redirectToRoute('app_index');
        }
        //Maintenant que nous avons notre Category, nous récupérons les Products qui lui sont associés
        $products = $category->getProducts();
       
            return $this->render('front/listcategory.html.twig', [
                'id' => $category->getId(),
                'products' => $products,
                 'categories' => $categories,
                'category' => $category,
            ]);
            
    
    }
    

    /**
    * @Route("/level/{levelId}", name="index_level")
    */
    public function indexLevel(CategoryRepository $categoryRepository, LevelRepository $levelRepository, $levelId): Response
    {
    //On récupère chaque level selon son Id
       $level=$levelRepository->find($levelId);
       //On récupère la liste des Categories
       $categories=$categoryRepository->findAll();
        //Maintenant que nous avons notre Category, nous récupérons les Products qui lui sont associés
        $products = $level->getProducts();
        
        return $this->render('front/listlevel.html.twig', [
            'level' => $level,
            'products' => $products,
            'categories'=>$categories
            
        ]);


    
    }

    /**
    * @Route("/methode", name="methode")
    */
    public function indexMethode(CategoryRepository $categoryRepository, ProductRepository $productRepository): Response
    {
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
    public function indexLudique(CategoryRepository $categoryRepository, ProductRepository $productRepository): Response
    {
        $categories=$categoryRepository->findAll();
        $products =  $productRepository->findAll();
        return $this->render('front/ludique.html.twig', [
            'products' => $products,
            'categories'=>$categories
            
        ]);
    }
}