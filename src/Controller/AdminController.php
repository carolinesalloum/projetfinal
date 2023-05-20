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
use App\Repository\ProductRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;



/**
 * Security('is_granted("ROLE_ADMIN")')
 * */
/**
 * @Route("/admin")
 */
class AdminController extends AbstractController
{
    /**
     * @Route("/", name="admin_backoffice")
     */

    public function adminBackoffice(ManagerRegistry $doctrine): Response
    {
        //Cette page affiche la liste des Products  avec la possibilité de les créer, de les modifier, et de les supprimer, totalisant ainsi les quatre fonctions du CRUD.

        //On récupère l'Entity Manager, et les Repository de Product et Tag
        $entityManager = $doctrine->getManager();
        $productRepository = $entityManager->getRepository(Product::class);
        //On récupère la liste de nos Products
        $products = array_reverse($productRepository->findAll());

        return $this->render('admin/admin_backoffice.html.twig', [
            'products' => $products,

        ]);
    }



    /**
     * @Route("/product/create", name="product_create")
     */
    public function createProduct(Request $request, ManagerRegistry $doctrine): Response
    {
        //Cette méthode nous permet de créer un Product grâce à un formulaire externalisé.

        $entityManager = $doctrine->getManager();
        //On crée une nouvelle Entity Product que nous lions à notre formulaire ProductType
        $product = new Product;
        $productForm = $this->createForm(ProductType::class, $product, ['file' => true]);
        //On applique l'objet Request sur notre formulaire
        $productForm->handleRequest($request);
        //On vérifie si notre formulaire est rempli et valide
        if ($productForm->isSubmitted() && $productForm->isValid()) {



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
           
            $entityManager->persist($product);
            $entityManager->flush();
            $this->addFlash('success', 'Produit crée');
            return $this->redirectToRoute('product_display');
        }
        //Si le formulaire n'est pas rempli, nous renvoyons l'Utilisateur vers ce dernier
        return $this->render('product/addproduct.html.twig', [
            'formName' => 'Création du Support',
            'dataForm' => $productForm->createView(),
            'product' => $product,
        ]);
    }


    /**
     *
     *
     * @Route("/product/display/all", name="product_display")
     */
    public function displayProductall(ProductRepository $productRepository ,ManagerRegistry $doctrine)
    {

        $products = $productRepository->findAll();

        //dd($products); // $products contient toutes les entrées de la table product en BDD
        $entityManager = $doctrine->getManager();
        $categoryRepository = $entityManager->getRepository(Category::class);
        $categories = $categoryRepository->findAll();

        return $this->render('product/displayproduct.html.twig', [
            'products' => $products,
            'categories' => $categories,

        ]);
    }
/**
     *
     *
     * @Route("/product/display/{{categoryId}}", name="product_displaycategory")
     */
    public function displayProductCategory($categoryId, ManagerRegistry $doctrine)
    {

        $entityManager = $doctrine->getManager();
        $categoryRepository = $entityManager->getRepository(Category::class);
       
        //On récupère la liste des Categories
        $categories = $categoryRepository->findAll();
      
        //Via le Repository, nous recherchons la Category qui nous intéresse. Si celle-ci n'existe pas, nous retournons à l'index
        $category = $categoryRepository->findOneBy(['id' => $categoryId]);
        
        if (!$category) {
            return $this->redirectToRoute('admin_backoffice');
        }
        //Maintenant que nous avons notre Category, nous récupérons les Products qui lui sont associés
        $products = $category->getProducts();
       
            return $this->render('product/displayproduct.html.twig', [
                'id' => $category->getId(),
                'products' => $products,
                 'categories' => $categories,
                'category' => $category,
            ]);
            

        //dd($products); // $products contient toutes les entrées de la table product en BDD

        

        
    }









    /**
     * @Route("/product/edit/{productId}", name="product_edit")
     */
    public function editProduct(Request $request, ManagerRegistry $doctrine, int $productId): Response
    {

        $entityManager = $doctrine->getManager();
        $productRepository = $entityManager->getRepository(Product::class);

        $product = $productRepository->find($productId);
        if (!$product) {
            return $this->redirectToRoute('admin_backoffice');
        }

        $productForm = $this->createForm(ProductType::class, $product, ['link' => true]);

        $productForm->handleRequest($request);

        if ($productForm->isSubmitted() && $productForm->isValid()) {


            $edit_file = $productForm->get('editFile')->getData();


            if ($edit_file) {

                $fileName = date('YmdHis') . $edit_file->getClientOriginalName();
                $edit_file->move($this->getParameter('upload_directory'), $fileName);
                unlink($this->getParameter('upload_directory') . '/' . $product->getFile());


                $product->setFile($fileName);
            }
            $entityManager->persist($product);
            $entityManager->flush();
            $this->addFlash('success', 'Produit modifié');
        }


        return $this->render('product/editproduct.html.twig', [
            'product' => $product,
            'dataForm' => $productForm->createView(),
        ]);
    }


    /**
     * @Route("/product/delete/{productId}", name="product_delete")
     */
    public function deleteProduct(Request $request, ManagerRegistry $doctrine, int $productId): Response
    {
        //Cette route permet la suppression d'un Product dont l'ID est renseigné par notre paramètre de route

        $entityManager = $doctrine->getManager();
        $productRepository = $entityManager->getRepository(Product::class);

        $product = $productRepository->find($productId);
        if (!$product) {
            return $this->redirectToRoute('admin_backoffice');
        }
        //Si le Product existe, nous procédons à sa suppression, et nous retournons au backoffice
        $entityManager->remove($product);
        $entityManager->flush();
        $this->addFlash('success', 'Produit supprimé');
        return $this->redirectToRoute('product_display');
    }

    // /**
    //  *
    //  *
    //  * @Route("/category/display/all", name="category_display")
    //  * */
    // public function displayCategory(Request $request,ManagerRegistry $doctrine)
    // {
    //     $entityManager=$doctrine->getManager();
    //     $categoryRepository= $entityManager->getRepository(Category::class);
    //     $categories = $categoryRepository->findAll();



    //     return $this->render('admin/admin_backoffice.html.twig', [
    //         'categories' => $categories,



    //     ]);
    // }

    /**
     *
     *
     * @Route("/category", name="category")
     * @Route("/category/edit/{categoryId}", name="category_edit")
     */
    public function createCategory(Request $request, ManagerRegistry $doctrine, int $categoryId = null)

    {
        $entityManager = $doctrine->getManager();
        $categoryRepository = $entityManager->getRepository(Category::class);

        $categories = $categoryRepository->findAll();
        //dd($categories)

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
            $entityManager->persist($category);
            //  On execute
            $entityManager->flush();
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
     * @Route("/category/delete/{categoryId}", name="category_delete")
     */
    public function deleteCategory(Request $request, ManagerRegistry $doctrine, int $categoryId): Response
    {
        $entityManager = $doctrine->getManager();
        $categoryRepository = $entityManager->getRepository(Category::class);
        $category = $categoryRepository->find($categoryId);

        if (!$category) {
            return $this->redirectToRoute('category');
        }
        $entityManager->remove($category);
        $entityManager->flush();
        $this->addFlash('success', 'Catégorie supprimé');
        return $this->redirectToRoute('category');
    }

    /**
     *
     *
     * @Route("/type", name="type")
     * @Route("/type/edit/{typeId}", name="type_edit")
     */
    public function createType(Request $request, ManagerRegistry $doctrine, int $typeId = null)

    {
        $entityManager = $doctrine->getManager();
        $typeRepository = $entityManager->getRepository(Type::class);

        $types = $typeRepository->findAll();

        if ($typeId) {
            $type = $typeRepository->find($typeId);
        } else {

            $type = new Type();
        }

        $typeForm = $this->createForm(TypeType::class, $type);

        $typeForm->handleRequest($request);

        if ($typeForm->isSubmitted() && $typeForm->isValid()) {

            $entityManager->persist($type);
            $entityManager->flush();

            if ($typeId) {
                $this->addFlash('success', 'format(type) modifiée');
            } else {

                $this->addFlash('success', 'format(type) ajoutée');
            }

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
     * @Route("/type/delete/{typeId}", name="type_delete")
     */
    public function deletetype(Request $request, ManagerRegistry $doctrine, int $typeId): Response
    {
        $entityManager = $doctrine->getManager();
        $typeRepository = $entityManager->getRepository(Type::class);
        $type = $typeRepository->find($typeId);

        if (!$type) {
            return $this->redirectToRoute('category');
        }
        $entityManager->remove($type);
        $entityManager->flush();
        $this->addFlash('success', 'Type supprimé');
        return $this->redirectToRoute('type');
    }

    /**
     *
     *
     * @Route("/level", name="level")
     * @Route("/level/edit/{levelId}", name="level_edit")
     */
    public function createLevel(Request $request, ManagerRegistry $doctrine, int $levelId = null)

    {
        $entityManager = $doctrine->getManager();
        $levelRepository = $entityManager->getRepository(Level::class);

        $levels = $levelRepository->findAll();

        if ($levelId) {
            $level = $levelRepository->find($levelId);
        } else {

            $level = new Level();
        }

        $levelForm = $this->createForm(LevelType::class, $level);

        $levelForm->handleRequest($request);

        if ($levelForm->isSubmitted() && $levelForm->isValid()) {

            $entityManager->persist($level);
            $entityManager->flush();

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
     * @Route("/level/delete/{levelId}", name="level_delete")
     */
    public function deletelevel(Request $request, ManagerRegistry $doctrine, int $levelId): Response
    {
        $entityManager = $doctrine->getManager();
        $levelRepository = $entityManager->getRepository(Level::class);
        $level = $levelRepository->find($levelId);

        if (!$level) {
            return $this->redirectToRoute('level');
        }
        $entityManager->remove($level);
        $entityManager->flush();
        $this->addFlash('success', 'niveau(level) supprimé');
        return $this->redirectToRoute('level');
    }
}
