<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\CommentsRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class AccountController extends AbstractController
{
    /**
     * @Route("/account", name="account")
     */
    public function index(CategoryRepository $categoryRepository ): Response
    {
        $categories= $categoryRepository->findAll();
        
                return $this->render('account/account.html.twig', [
            'categories' => $categories,
            // 'user' => $user
        ]);
    }
    /**
     * @Route("/account/profile", name="profile")
     */
    public function profile(CategoryRepository $categoryRepository,CommentsRepository $commentsRepository ):Response
    {

        $categories= $categoryRepository->findAll();
    $comments = $commentsRepository->findAll();
        return $this->render('account/profile.html.twig', [
            'categories' => $categories,
            'comments'=> $comments
        ]);
    }
      /**
     * @Route("/account/changepassword", name="users_pass_modifier")
     */
    public function editPass(Request $request, UserPasswordHasherInterface $passHasher,EntityManagerInterface $em,CategoryRepository $categoryRepository)
    {
        
        $categories= $categoryRepository->findAll();
        // on vérifie si on est en méthode POST 
        if($request->isMethod('POST')){
           // on recupère l'utilisateur puisque on a besoin de changer son mot de pass
            $user = $this->getUser();

            // On vérifie si les 2 mots de passe sont identiques
            if($request->request->get('pass') == $request->request->get('pass2')){
                $user->setPassword($passHasher->hashPassword($user, $request->request->get('pass')));
                $em->flush();
                $this->addFlash('success', 'Mot de passe mis à jour avec succès');

                return $this->redirectToRoute('profile');
            }else{
                $this->addFlash('danger', 'Les deux mots de passe ne sont pas identiques');
            }
        }

        return $this->render('account/editpass.html.twig',[
            'categories' => $categories,
        ]);
    }
    
     /**
     * @Route("/account/comments/", name="user_comments")
     */
    public function accountComments(CommentsRepository $commentsRepository,CategoryRepository $categoryRepository,UserRepository $userRepository  ): Response
    {
         $categories = $categoryRepository->findAll();
        // $user= $userRepository->find($userId);
        // $user->getComments();
        // $comments = $commentsRepository->findBy(['active' => 1]);
        
        
        // return $this->render('account/account-comments.html.twig', [
        //              'comments' => $comments,
        //              'categories'=> $categories,
        //              'user' => $user
        // ]);
        $user = $this->getUser();
        $comments = $user->getComments();
        return $this->render('account/account-comments.html.twig', [
                         'comments' => $comments,
                         'categories'=> $categories,
        ]);
        }
}
