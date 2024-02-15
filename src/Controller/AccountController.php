<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



class AccountController extends AbstractController
{
    /**
     * @Route("/account", name="account")
     */
    public function index(CategoryRepository $categoryRepository ): Response
    {
        // // on récupère le user
        //  $user= $userRepository->find($userId);
        $categories = $categoryRepository->findAll();
        $user=$this->getUser();

     if (!$user) {
        throw $this->createNotFoundException('Utilisateur non trouvé');
    }
     $comments = $user->getComments();
        // //à partir de notre user on récupère ses (avis) comments 
        // $comments = $user->getComments();

              return $this->render('account/account.html.twig', [
            'comments'=> $comments,
            'categories' =>$categories,
             'user' => $user,
        ]);
    }
    
    
     /**
     * @Route("/account/delete", name="delete_account")
     */
    public function deletAccount(EntityManagerInterface $em ): Response
    {
        // Récupérez l'utilisateur actuellement connecté
        $user = $this->getUser();
      
 
   if (!$user) {
    throw $this->createNotFoundException('Utilisateur non trouvé.');
    }      


      // Supprimer les commentaires associés à l'utilisateur
    $comments = $user->getComments();
    foreach ($comments as $comment) {
        $em->remove($comment);
    }

    //  Supprimer l'utilisateur
    $em->remove($user);

    //  éxecuter la requete dans BDD
    $em->flush();
    // Invalidez la session de l'utilisateur pour le déconnecter
    $this->get('security.token_storage')->setToken(null);

    //  ajouter un flash message 
    $this->addFlash('success', 'Votre compte utilisateur a bien été supprimé !');

    //  Redirection vers la page d'accueil après la suppression du compte
    return $this->redirectToRoute('app_index');
}





}
