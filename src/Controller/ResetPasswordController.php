<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Form\ResetPasswordFormType;
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
    public function forgottenPassword( Request $request,UserRepository $usersRepository, TokenGeneratorInterface $tokenGenerator,
    EntityManagerInterface $entityManager,MailerInterface $mailer ): Response
    {
        
        $form = $this->createForm(ResetPasswordRequestFormType::class);
        //traiter  les donnée de formulaire
        $form->handleRequest($request);
        //dd($form);
        // on vérifie si le formulaire est remplie et valide
        if($form->isSubmitted() && $form->isValid()){
            //On va chercher l'utilisateur par son email
            $user = $usersRepository->findOneByEmail($form->get('email')->getData());
           //dd($user);

          // On vérifie si on a un utilisateur
            if($user){ 
                // On génère un token de réinitialisation pour identifier l'utilisateur
            $token = $tokenGenerator->generateToken();
           //dd($token);
            //setResetToken est une méthode existe dans l'entité user qui nous permet de définir la valeur du jeton de réinitialisation.
            $user->setResetToken($token);
            // Cette ligne indique à EntityManager de commencer à suivre l'objet $user ,cela signifie que toutes les modifications apportées à cet objet seront enregistrées dans la base de données lorsque vous appellerez 
            //ultérieurement$entityManager->flush()
            $entityManager->persist($user);
            $entityManager->flush();

             // On génère un lien de réinitialisation du mot de passe
                $url = $this->generateUrl('reset_pass', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);//absouloute :url complette 
                
                // On crée les données du mail
                //on cherche l'utilisateur
                $userMail = $user->getEmail();
                // pour definir le centenue de notre email avec twig ,on utlilise la class TemplatedEmail() 
                $email = (new TemplatedEmail())
                ->from('info@francoarabophone.fr')
                ->to($userMail)
                ->subject('Réinitialisation de mot de passe')
                ->htmlTemplate('emails/reset_pass.html.twig')// Indique le modèle Twig utilisé pour le contenu de l'e-mail
                ->context([ //Fournit des données à utiliser dans le modèle Twig
                    // pass variables (name => value) to the template
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
        UserPasswordHasherInterface $passwordHasher
       
    ): Response
    { //on a besoin de cette méthode pour générer le lien de réinstallation le mot de pass
        
    
        // On vérifie si on a ce token dans la base
        $user = $usersRepository->findOneByResetToken($token);
        // On vérifie si on a un utilisateur
        if($user){

            $form = $this->createForm(ResetPasswordFormType::class);
            //traiter les donnée de formulaire
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
               
            ]);
        }
        $this->addFlash('danger', 'Jeton invalide');
        return $this->redirectToRoute('app_login');
    }
}