<?php

namespace App\Controller;

use App\Form\ChangePasswordType;
use App\Service\SendMailService;
use App\Repository\UserRepository;
use App\Form\ResetPasswordFormType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\ResetPasswordRequestFormType;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;


class ResetPasswordController extends AbstractController{
/**
     *
     *
     * @Route("/oubli-pass", name="forgotten_password")
     */
    public function forgottenPassword(
        Request $request,
        UserRepository $usersRepository,
        TokenGeneratorInterface $tokenGenerator,
        EntityManagerInterface $entityManager,

        MailerInterface $mailer ,
        CategoryRepository $categoryRepository
    ): Response
    {
        $categories = $categoryRepository->findAll();
        $form = $this->createForm(ResetPasswordRequestFormType::class);
        //récupérer les donnée de formulaire
        $form->handleRequest($request);
        //dd($form);
        if($form->isSubmitted() && $form->isValid()){
            //On va chercher l'utilisateur par son email
            $user = $usersRepository->findOneByEmail($form->get('email')->getData());
           //dd($user);

          // On vérifie si on a un utilisateur
            if($user){ 
                // On génère un token de réinitialisation pour identifier l'utilisateur
            $token = $tokenGenerator->generateToken();
           //dd($token);
            
            $user->setResetToken($token);
            $entityManager->persist($user);
            $entityManager->flush();

             // On génère un lien de réinitialisation du mot de passe
                $url = $this->generateUrl('reset_pass', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);//absouloute :url complette 
                
                // On crée les données du mail
                // $context = compact('url', 'user');
                $userMail = $user->getEmail();
                $email = (new TemplatedEmail())// pour definir le centenue de notre email avec twig ,on utlilise la class TemplatedEmail() 
                ->from('alsalloumcaroline@gmail.com')
                ->to($userMail)
                ->subject('Réinitialisation de mot de passe')
                ->htmlTemplate('emails/reset_pass.html.twig')// path of the Twig template to render
                ->context([ // pass variables (name => value) to the template
                    'url' => $url,
                    'user' => $user->getNickname(),
                ])
                ;
                // Envoi du mail
                $mailer->send($email);
                //user exist alors on a le message suivant et on retourne sur le login 
                $this->addFlash('success', 'Email envoyé avec succès');
                return $this->redirectToRoute('app_login');
            }
            // $user est null 
            $this->addFlash('danger','Un problème est survenu');
            //on retourne sur le login
            return $this->redirectToRoute('app_login');
        
        }

        return $this->render('reset_password/request.html.twig', [
            'requestPassForm' => $form->createView(),
            'categories' => $categories
        ]);
    }
/**
     *
     *
     * @Route("/oubli-pass/{token}", name="reset_pass")
     */
    

    public function resetPass(
        string $token,
        Request $request,
        UserRepository $usersRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        CategoryRepository $categoryRepository
    ): Response
    { //on a besoin de cette méthode pour générer le lien de réinstallation le mot de pass
        
        $categories = $categoryRepository->findAll();
        // On vérifie si on a ce token dans la base
        $user = $usersRepository->findOneByResetToken($token);
        // On vérifie si on a un utilisateur
        if($user){

            $form = $this->createForm(ResetPasswordFormType::class);
            //récupérer les donnée de formulaire
            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){
                // On efface le token
                $user->setResetToken('');
                //encoder le mot de pass
                $user->setPassword(
                    $passwordHasher->hashPassword(
                        $user,
                        $form->get('password')->getData()//chercher le mot de pass
                    )
                );
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Mot de passe changé avec succès');
                return $this->redirectToRoute('app_login');
            }

            return $this->render('reset_password/reset.html.twig', [
                'resetForm' => $form->createView(),
                'categories' => $categories
            ]);
        }
        $this->addFlash('danger', 'Jeton invalide');
        return $this->redirectToRoute('app_login');
    }
}