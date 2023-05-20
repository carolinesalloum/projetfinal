<?php

namespace App\Controller;

use App\Entity\Level;
use App\Entity\Product;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\LevelRepository;
use App\Repository\ProductRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MainController extends AbstractController
{
   /**
    * @Route("/", name="app_index")
    */
    public function index(ManagerRegistry $doctrine, LevelRepository $repoLevel): Response
    {
        //Afficher les lesvels et categories
      
        $entityManager = $doctrine->getManager();
         $levels=$repoLevel->findAll();
        
        $categoryRepository = $entityManager->getRepository(Category::class);
        $categories = $categoryRepository->findAll();
       
      
        return $this->render('index/index.html.twig', [
            'categories' => $categories,
           
            'levels'=>$levels
        ]);
    }
   
    /**
    * @Route("/category/{categoryId}", name="index_category")
    */
    public function indexCategory($categoryId, ManagerRegistry $doctrine ): Response
    {
        $entityManager = $doctrine->getManager();
        $categoryRepository = $entityManager->getRepository(Category::class);
       
        //On récupère la liste des Categories
        $categories = $categoryRepository->findAll();
      
        //Via le Repository, nous recherchons la Category qui nous intéresse. Si celle-ci n'existe pas, nous retournons à l'index
        $category = $categoryRepository->findOneBy(['id' => $categoryId]);
        
        if (!$category) {
            return $this->redirectToRoute('app_index');
        }
        //Maintenant que nous avons notre Category, nous récupérons les Products qui lui sont associés
        $products = $category->getProducts();
       
            return $this->render('index/listcategory.html.twig', [
                'id' => $category->getId(),
                'products' => $products,
                 'categories' => $categories,
                'category' => $category,
            ]);
            
            

    

    
    }
    // /**
    // * @Route("/category/activity/{categoryId}", name="index_activity")
    // */
    // public function indexActivity(ManagerRegistry $doctrine ,$categoryId ): Response
    // {
    //      $entityManager = $doctrine->getManager();

    //     $productRepository = $entityManager->getRepository(Product::class);
       
    //     $products =  $productRepository->findAll();
    //      $product = $productRepository->find('$productId');
    //     $categoryRepository = $entityManager->getRepository(Category::class);
    //     $categories = $categoryRepository->findAll();
    //     $category = $categoryRepository->findOneBy(['id' => $categoryId]);
     
          

    //     return $this->render('index/listactivity.html.twig', [
    //         'id' => $category->getId(),
    //         'products' => $products,
    //         'product' => $product,
    //         'categories' => $categories,
    //         'category' => $category,
            
    //     ]);
    // }
    


    /**
    * @Route("/level/{levelId}", name="index_level")
    */
    public function indexLevel(CategoryRepository $categoryRepository, LevelRepository $levelRepository, $levelId): Response
    {
    // dd($levelId);
       $level=$levelRepository->find($levelId);
       $categories=$categoryRepository->findAll();
        //Maintenant que nous avons notre Category, nous récupérons les Products qui lui sont associés
        $products = $level->getProducts();
        
        return $this->render('index/listlevel.html.twig', [
            'level' => $level,
            'products' => $products,
            'categories'=>$categories
            
        ]);



    
    }
   

}