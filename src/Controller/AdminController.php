<?php

namespace App\Controller;

use DateTime;
use App\Entity\Type;
use App\Entity\Level;
use App\Form\TypeType;
use App\Entity\Product;
use App\Form\LevelType;
use App\Entity\Category;
use App\Form\ProductType;
use App\Form\CategoryType;
use App\Repository\TypeRepository;
use App\Repository\LevelRepository;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use App\Repository\CommentsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;





class AdminController extends AbstractController
{
    /**
     * @IsGranted("ROLE_ADMIN")
     * 
     * @Route("/admin", name="admin_backoffice")
     */

    public function adminBackoffice(ProductRepository $productRepository,CategoryRepository $categoryRepository): Response
    {
        //Cette page affiche la liste des Products  avec la possibilité de les créer, de les modifier, et de les supprimer, totalisant ainsi les quatre fonctions du CRUD
        
        //On récupère la liste de nos Products
        $products = $productRepository->findAll();
        //On récupère la liste de nos categories
        $categories = $categoryRepository->findAll();
        return $this->render('admin/admin-backoffice.html.twig', [
            'products' => $products,
            'categories' => $categories
        ]);
    }



    /**
     * @Route("/admin/product/create", name="product_create")
     */
    public function createProduct(Request $request, EntityManagerInterface $manager): Response
    {
        //Cette méthode nous permet de créer un Produit grâce à un formulaire externalisé.
        // EntityManagerInterface $manager obligatoire pour toutes les requêtes d'INSERT INTO, UPDATE, DELETE
       
        //On crée une nouvelle Entity Product que nous lions à notre formulaire ProductType
        $product = new Product;
        // $productForm est un objet instance de Form
        $productForm = $this->createForm(ProductType::class, $product, ['file' => true]);
        //On applique l'objet Request sur notre formulaire pour récupérer les données provenants du formulaire et charger l'objet Product
        $productForm->handleRequest($request);
        //On vérifie si notre formulaire est rempli et valide
        if ($productForm->isSubmitted() && $productForm->isValid()) {
            //Pour gérer les uploads de photos, nous créons un nom aléatoire pour le fichier. Ensuite, nous déplaçons le fichier uploadé à son emplacement final (le répertoire photo). Enfin, nous stockons le nom du fichier dans l'objet ptoduct
            // on récupère toutes les données sur l'input type file 
            $file = $productForm->get('file')->getData();
            if (!empty($file)) :
                $fileName = (new DateTime())->format('Ymd-His') . '_' . $file->getClientOriginalName();
              
                try {
                    $file->move($this->getParameter('upload_directory'), $fileName);
                   
                } catch (FileException $e) {
                    dd($e);
                }

            else :
                 $fileName=$productForm->get('url')->getData(); 
            endif;
            $product->setFile($fileName);
           // on lui demande de persister l'objet (préparation de la requête)
            $manager->persist($product);
             // on envoie l'objet en BDD (execute )
            $manager->flush();
            $this->addFlash('success', 'Produit crée');
            return $this->redirectToRoute('admin_backoffice');
        }
        //Si le formulaire n'est pas rempli, nous renvoyons l'Utilisateur vers ce dernier
        return $this->render('admin/addproduct.html.twig', [
            'formName' => 'Création du Support',
            'dataForm' => $productForm->createView(),
            'product' => $product,
        ]);
    }



 /**
     *
     *
     * @Route("/admin/product/display/{categoryId}", name="product_display")
     */
    public function displayProductCategory(int $categoryId, CategoryRepository $categoryRepository)
    {

        //On récupère la liste des Categories
        $categories = $categoryRepository->findAll();
      
        //Via le Repository, nous recherchons la Category qui nous intéresse. Si celle-ci n'existe pas, nous retournons à l'index
        $category = $categoryRepository->findOneBy(['id' => $categoryId]);
        
        if (!$category) {
            return $this->redirectToRoute('admin_backoffice');
        }
        //Maintenant que nous avons notre Category, nous récupérons les Products qui lui sont associés
        $products = $category->getProducts();
       
            return $this->render('admin/admin-backoffice.html.twig', [
                  
                'products' => $products,
                 'categories' => $categories,
                'category' => $category,
            ]);
        
        
    }


    /**
     * @Route("/admin/product/edit/{productId}", name="product_edit")
     */
    public function editProduct(Request $request,ProductRepository $productRepository , EntityManagerInterface $manager, int $productId): Response
    {
// lorsque un paramètre id est passé sur l'url et l'on injecte en dépendance une entité voulue (ici Product), symfony rempli automatique l'objet $product de ses données sur l'id passé (SELECT * FROM product WHERE id={id})
// nous sommes en modification donc pas d'instanciation de nouvel objet. (pas de new Product)
        $product = $productRepository->find($productId);
        if (!$product) {
            return $this->redirectToRoute('admin_backoffice');
        }
        // on récupère ses donnés de la BDD
        $productForm = $this->createForm(ProductType::class, $product, ['link' => true]);
        //On applique la méthode handleRequest sur notre formulaire
        $productForm->handleRequest($request);
        //Si le formulaire est valide et rempli, nous persistons son Product lié
        if ($productForm->isSubmitted() && $productForm->isValid()) {

            $edit_file = $productForm->get('editFile')->getData();
            // on verifie si le champs editFile a été saisi. alors on modifie la propriété file
            if ($edit_file) {
                $fileName = date('YmdHis') . $edit_file->getClientOriginalName();
                // on copie le nouveau fichier et supprime le précédent
                $edit_file->move($this->getParameter('upload_directory'), $fileName);
                unlink($this->getParameter('upload_directory') . '/' . $product->getFile());

                $product->setFile($fileName);
            }
            $manager->persist($product);
            $manager->flush();
            $this->addFlash('success', 'Produit modifié');
            return $this->redirectToRoute('admin_backoffice');
        }
        return $this->render('admin/editproduct.html.twig', [
            'product' => $product,
            'dataForm' => $productForm->createView(),
        ]);
    }


    /**
     * @Route("/admin/product/delete/{productId}", name="product_delete")
     */
    public function deleteProduct( EntityManagerInterface $manager,ProductRepository $productRepository , int $productId): Response
    {
        //Cette route permet la suppression d'un Product dont l'ID est renseigné par notre paramètre de route
        $product = $productRepository->find($productId);
        //on vérifie que le product exist
        if (!$product) {
            return $this->redirectToRoute('product_display');
        }
        //Si le Product existe, nous procédons à sa suppression, et nous retournons au backoffice
        $manager->remove($product);
        $manager->flush();
        $this->addFlash('success', 'Produit  supprimé');
        return $this->redirectToRoute('admin_backoffice');
    }

    /**
     *
     *
     * @Route("/admin/category", name="category")
     * @Route("/admin/category/edit/{categoryId}", name="category_edit")
     */
    public function createCategory(Request $request, EntityManagerInterface $manager, CategoryRepository $categoryRepository, int $categoryId = null)

    {
         //On récupère la liste des Categories
        $categories = $categoryRepository->findAll();

        // création d'un nouvel objet instance de Category pour l'ajout
        if ($categoryId) {  // si $id n'est pas null on est sur la route editCategory
            $category = $categoryRepository->find($categoryId);
        } else { // sinon on est sur la route category donc en création

            $category = new Category();
        }

        // Création du formulaire en liens avec Category
        $categoryForm = $this->createForm(CategoryType::class, $category);

        // on appelle la méthode handleRequest sur notre objet formulaire pour récupérer les données provenants du formulaire et charger l'objet Category
        $categoryForm->handleRequest($request);

        // condition de soumission et de validité du formulaire
        if ($categoryForm->isSubmitted() && $categoryForm->isValid()) {
            // L'objet category est rempli de toutes ses information (pas besoin d'utiliser certains de ses setters pour lui attribuer des valeurs)
            // On demande au manager de préparer la requête
            $manager->persist($category);
            //  On execute
            $manager->flush();
            // message en session
            if ($categoryId) {
                $this->addFlash('success', 'Catégorie modifiée');
            } else {

                $this->addFlash('success', 'Catégorie ajoutée');
            }
            // return d'une redirection sur le twig appelé category (en name de public fonction)
            return $this->redirectToRoute('category');
        }
        // on renvoie la vue du formulaire grace à la méthode createView()
        return $this->render('admin/category.html.twig', [
            'categoryForm' => $categoryForm->createView(),
            'categories' => $categories

        ]);
    }

    /**
     *
     * @Route("/admin/category/delete/{categoryId}", name="category_delete")
     */
    public function deleteCategory(EntityManagerInterface $manager,CategoryRepository $categoryRepository, int $categoryId): Response
    {
         //On récupère la liste des Categories
        $category = $categoryRepository->find($categoryId);
        //on vérifie que le catégorie exist'
        if (!$category) {
            return $this->redirectToRoute('category');
        }
        //Si le categorie existe, nous procédons à sa suppression, et nous retournons à la page affichage de categories
        $manager->remove($category);
        $manager->flush();
        $this->addFlash('success', 'Catégorie supprimé');
        return $this->redirectToRoute('category');
    }

    /**
     *
     *
     * @Route("/admin/type", name="type")
     * @Route("/admin/type/edit/{typeId}", name="type_edit")
     */
    public function createType(Request $request,TypeRepository $typeRepository,EntityManagerInterface $manager , int $typeId = null)

    {
        //On récupère la liste des types
        $types = $typeRepository->findAll();
        // si $id n'est pas null on est sur la route type_edit
        if ($typeId) {
            $type = $typeRepository->find($typeId);
        } else {
            // création d'un nouvel objet instance de Type pour l'ajout
            $type = new Type();
        }
        // Création du formulaire en liens avec Category
        $typeForm = $this->createForm(TypeType::class, $type);
        // on appelle la méthode handleRequest sur notre objet formulaire pour récupérer les données provenants du formulaire et charger l'objet Type
        $typeForm->handleRequest($request);
        // condition de soumission et de validité du formulaire
        if ($typeForm->isSubmitted() && $typeForm->isValid()) {

            // On demande au manager de préparer la requête
            $manager->persist($type);
              //  On execute
            $manager->flush();
            // message in session 
            if ($typeId) {
                $this->addFlash('success', 'format(type) modifiée');
            } else {

                $this->addFlash('success', 'format(type) ajoutée');
            }
        // return d'une redirection sur le twig appelé type
            return $this->redirectToRoute('type');
        }

        // on renvoie la vue du formulaire grace à la méthode createView()
        return $this->render('admin/type.html.twig', [
            'typeForm' => $typeForm->createView(),
            'types' => $types

        ]);
    }

    /**
     *
     * @Route("/admin/type/delete/{typeId}", name="type_delete")
     */
    public function deletetype(TypeRepository $typeRepo,EntityManagerInterface $manager, int $typeId): Response
    {

        $type = $typeRepo->find($typeId);

        if (!$type) {
            return $this->redirectToRoute('category');
        }
        $manager->remove($type);
        $manager->flush();
        $this->addFlash('success', 'Type supprimé');
        return $this->redirectToRoute('type');
    }

    /**
     *
     *
     * @Route("/admin/level", name="level")
     * @Route("/admin/level/edit/{levelId}", name="level_edit")
     */
    public function createLevel(Request $request,LevelRepository $levelRepo, EntityManagerInterface $manager, int $levelId = null)

    {
        $levels = $levelRepo->findAll();

        if ($levelId) {
            $level = $levelRepo->find($levelId);
        } else {

            $level = new Level();
        }

        $levelForm = $this->createForm(LevelType::class, $level);

        $levelForm->handleRequest($request);

        if ($levelForm->isSubmitted() && $levelForm->isValid()) {

            $manager->persist($level);
            $manager->flush();

            if ($levelId) {
                $this->addFlash('success', 'niveau(level) modifiée');
            } else {

                $this->addFlash('success', 'niveau(level) ajoutée');
            }

            return $this->redirectToRoute('level');
        }

        // on renvoie la vue du formulaire grace à la méthode createView()
        return $this->render('admin/level.html.twig', [
            'levelForm' => $levelForm->createView(),
            'levels' => $levels

        ]);
    }

    /**
     *
     * @Route("/admin/level/delete/{levelId}", name="level_delete")
     */
    public function deletelevel(LevelRepository $levelRepository, EntityManagerInterface $manager, int $levelId): Response
    {
    
        $level = $levelRepository->find($levelId);

        if (!$level) {
            return $this->redirectToRoute('level');
        }
        $manager->remove($level);
        $manager->flush();
        $this->addFlash('success', 'niveau(level) supprimé');
        return $this->redirectToRoute('level');
    }
     /**
     *
     * @Route("/admin/comments", name="admin_comments")
     */
    // afficher les comments 
     public function comments(CommentsRepository $commentsRepository,CategoryRepository $categoryRepository ): Response
    {
        $comments = $commentsRepository->findAll();
        $categories = $categoryRepository->findAll();
        return $this->render('admin/comments.html.twig', [
                     'comments' => $comments,
                     'categories'=> $categories,
        ]);
         }
    


 /**
     *
     * @Route("/admin/comments/delete/{id}", name="admin_delet_comments")
     */
    public function commentsDelete(CommentsRepository $commentsRepository,int $id ,EntityManagerInterface $manager): Response
    {

     $comment = $commentsRepository->find($id);

     if (!$comment) {
         return $this->redirectToRoute('admin_backoffice');
     }
     $manager->remove($comment);
     $manager->flush();
     $this->addFlash('success', 'avis est supprimé');
     return $this->redirectToRoute('admin_comments');
 }

 /**
     *
     * @Route("/admin/comments/accept/{id}", name="admin_accept_comments")
     */
    public function commentsAccept(CommentsRepository $commentsRepository,CategoryRepository $categoryRepository,int $id ,EntityManagerInterface $manager): Response
    {
        $comment = $commentsRepository->find($id);
        if (!$comment) {
            return $this->redirectToRoute('admin_backoffice');
        }
        $comment->setActive(true);
        $manager->persist($comment);
        $manager->flush();
        $this->addFlash('success', 'avis est accepté');
        return $this->redirectToRoute('admin_comments');
    }


}
