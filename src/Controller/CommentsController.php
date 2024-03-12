<?php

namespace App\Controller;




use DateTimeImmutable;
use App\Entity\Comments;
use App\Form\CommentsType;
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
    // si l'utilisateur n'est pas activé ,afficher la page d'erreur 'compte-non-valide',sinon l'utilisateur peux ajouter un commenaire
    if (!$user->getIsVerified()) {
        return $this->redirectToRoute('nonvalide');
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
         $comment->setUser($this->getUser());// chercher le commentaire de l'utilisateur connecté
       
        $manager->persist($comment);//préparer la requete 
        $manager->flush();//sauvegarder dans la base de données

        $this->addFlash('success', 'Votre commentaire est en train de validation.');

        // Redirect to the appropriate route or display a success message
        return $this->redirectToRoute('app_comments');
    }

    return $this->render('comments/addcomments.html.twig', [
      
        'commentForm' => $commentForm->createView(),
        
    ]);
}



/**
 * @Route("/comments/delete", name="delete_comment")
 *
 */
public function deleteComment(EntityManagerInterface $em ): Response
{
$user = $this->getUser();
      
     

   // Supprimer les commentaires associés à l'utilisateur
 $comments= $user->getComments();
 foreach ($comments as $comment) {
     $em->remove($comment);
 }

 //  éxecuter la requete dans BDD
 $em->flush();

 $this->addFlash('success', 'votre commentaire a bien été supprimé.');
    return $this->redirectToRoute('app_comments');

}
}