<?php

namespace App\Controller;


use App\Entity\User;
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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CommentsController extends AbstractController
{

   


    /**
     * @Route("/comments", name="app_comments")
     */
    public function index(CommentsRepository $commentsRepository,CategoryRepository $categoryRepository ): Response
    {   
        //on récupère les comments qui ont été déja acceptés par l'Admin
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
public function addComment(Request $request, EntityManagerInterface $manager, CategoryRepository $categoryRepository, UserRepository $userRepository ): Response
{
    //on récupère le user
    $user= $this->getUser();
    //on protège cette route par données la possibilité de (contacter) jusque aux utilisateurs après avoir connectées
    if (!$user) {
        //si l'utilisateur n"est pas connecté on envoi une message et on le dirige vers la page de connexion 
        $this->addFlash('danger', 'Veuillez vous connecter pour ajouter un commentaire.');
        return $this->redirectToRoute('app_login');
    }
    
    $categories = $categoryRepository->findAll();
    //pour ajouter une commentaire , il faut instencier un objet comment
    $comment = new Comments();
    
    $commentForm = $this->createForm(CommentsType::class, $comment);
    $commentForm->handleRequest($request);

    if ($commentForm->isSubmitted() && $commentForm->isValid()) {
        $comment->setActive(false);
        $comment->setCreatedAt(new DateTimeImmutable());
         $comment->setUser($this->getUser());
       
        $manager->persist($comment);
        $manager->flush();

        $this->addFlash('success', 'Votre commentaire a bien été ajouté.');

        // Redirect to the appropriate route or display a success message
        return $this->redirectToRoute('app_comments');
    }

    return $this->render('comments/addcomments.html.twig', [
        'categories' => $categories,
        'commentForm' => $commentForm->createView(),
        
    ]);
}

// /**
//  * @Route("/comments/edit", name="edit_comment")
//  */
// public function editComment(Request $request , CategoryRepository $categoryRepository,EntityManagerInterface $em , CommentsRepository $comment): Response
// {$categories = $categoryRepository->findAll();
//     // $this->denyAccessUnlessGranted('comment_edit', $comment);
//     $comment = new Comments;
//     $form = $this->createForm(CommentsType::class, $comment);

//     $form->handleRequest($request);

//     if($form->isSubmitted() && $form->isValid()){
//         $comment->setActive(false);
//         $comment->setContent($form->get('content')->getData());
        
//         // $em = $this->getDoctrine()->getManager();
//         $em->persist($comment);
//         $em->flush();

//         return $this->redirectToRoute('account');
//         $this->addFlash(
//            'success','success','vtre avis a bien été modifié');
//     }

//     return $this->render('comments/edit-comments.html.twig', [
//         'form' => $form->createView(),
//         'comment' => $comment,
//         'categories' => $categories,
//     ]);
// }





}









