<?php

namespace App\Controller;



use DateTimeImmutable;
use App\Entity\Comments;
use App\Form\CommentsType;
use App\Repository\UserRepository;
use App\Repository\CategoryRepository;
use App\Repository\CommentsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CommentsController extends AbstractController
{

   


    /**
     * @Route("/comments", name="app_comments")
     */
    public function index(CommentsRepository $commentsRepository,CategoryRepository $categoryRepository ): Response
    {   
        //on récupère les comments qui ont été déja acceptés par l'Admin (page de Témoingage)
        $comments = $commentsRepository->findBy(['active' => 1]);
    // on récupère les catégories
        $categories = $categoryRepository->findAll();

        return $this->render('comments/listcomments.html.twig', [
                     'comments' => $comments,
                     'categories'=> $categories,
        ]);
         }


/**
 * @Route("/comments/add", name="add_comment")
 *
 */
public function addComment(Request $request, EntityManagerInterface $manager ): Response
{
    // `$this->getUser()` est une méthode utilisée pour récupérer l'utilisateur actuellement authentifié 
    // Cette méthode est fournie par le composant Sécurité de Symfony et renvoie un objet utilisateur si l'utilisateur est authentifié, ou `null` s'il n'y a pas d'utilisateur authentifié.
    $user= $this->getUser();
    //on protège cette route par donnéer la possibilité de (contacter) que aux utilisateurs après avoir connectées
    if (!$user) {
        //si l'utilisateur n"est pas connecté on envoi une message et on le dirige vers la page de connexion 
        $this->addFlash('danger', 'Veuillez vous connecter pour ajouter un commentaire.');
        return $this->redirectToRoute('app_login');
    }
    
    
    //pour ajouter une commentaire , il faut instencier un objet comment
    $comment = new Comments();
    //ccréation de formulaire en lien avec entité Comment 
    $commentForm = $this->createForm(CommentsType::class, $comment);
    //traiter les données par handle request 
    $commentForm->handleRequest($request);
    //vérifier que le form est valid et remplie
    if ($commentForm->isSubmitted() && $commentForm->isValid()) {
        
        $comment->setActive(false);//par cet étape on empêche les commentairs d'appraître sans la permission de l'Admin 

        $comment->setCreatedAt(new DateTimeImmutable());
         $comment->setUser($this->getUser());
       
        $manager->persist($comment);
        $manager->flush();

        $this->addFlash('success', 'Votre commentaire a bien été ajouté.');

        // Redirect to the appropriate route or display a success message
        return $this->redirectToRoute('app_comments');
    }

    return $this->render('comments/addcomments.html.twig', [
      
        'commentForm' => $commentForm->createView(),
        
    ]);
}







}