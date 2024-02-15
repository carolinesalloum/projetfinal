<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\JWTService;
use App\Form\RegistrationType;
use App\Repository\UserRepository;
use App\Security\LoginAuthentificator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;


class RegistrationController extends AbstractController
{

    

      /**
     * @Route("/register", name="register")
     */
    public function registerUser(Request $request, LoginAuthentificator $authenticator,UserAuthenticatorInterface $userAuthenticator,EntityManagerInterface $manager, UserPasswordHasherInterface $passHasher, MailerInterface $mailer,JWTService $jwt): Response

    {
        //Cette méthode permet la création d'un compte Client via formulaire
    
        //Nous créons et renseignons notre Utilisateur
        $user = new User;
        //Nous appele la formulaire  pour l'inscription
        $userForm = $this->createForm(RegistrationType::class, $user);
        //On applique la Request sur notre formulaire
        $userForm->handleRequest($request);
        //On se prépare à utiliser le formulaire
        if ($userForm->isSubmitted() && $userForm->isValid()) {
            //On récupère les informations de notre formulaire

            $user->setNickname($userForm->get('nickname')->getData());
            $user->setEmail($userForm->get('email')->getData());
            $user->setAge($userForm->get('age')->getData());
            $user->setRoles(['ROLE_USER']);
            $user->setIsVerified(false);
            $user->setPassword($passHasher->hashPassword($user, $userForm->get('password')->getData()));
            //On persiste notre Entity
             $manager->persist($user);
             $manager->flush();
        //on génère le JWT de l'utilisateur
        //on crée le header
            $header = [
                'alg'=> 'HS256',
                'typ' => 'JWT'
            ];
        // on crée le payload 
        $payload = [
            'user_id' => $user->getId()
        ];
        // on génère le token 
        $token = $jwt->generate($header , $payload,
        $this->getParameter('app.jwtsecret'));// on passe le secret avec $this->getParameter('app.jwtsecret') qui éxiste dans le fichier service.yaml+xw C w cw 

        //dd($token);
            // mailer
                $userMail = $user->getEmail();
              $email = (new TemplatedEmail())
              ->from('info@francoarabophone.fr')
              ->to($userMail)
              ->subject('Activer votre compte')
              ->htmlTemplate('emails/registration.html.twig')
              ->context([
                'user' => $user->getNickname(),
                'token' => $token
            ]);
               
    
            $mailer->send($email);
             //Après le transfert de notre Entity User, on retourne sur le login
            return $this->redirectToRoute('app_login');
            return $userAuthenticator->authenticateUser( $user,$authenticator, $request );//utilisé pour authentifier un utilisateur  en appelant une méthode AuthenticateUser personnalisée sur le service ou la classe $userAuthenticator.
        
           
           
        }
        //Si notre formulaire n'est pas validé, nous le présentons à l'Utilisateur
        return $this->render('register/register.html.twig', [
            // 'formName' => 'Inscription Utilisateur',
            'userForm' => $userForm->createView(),
            
        ]);
    }


    /**
     * @Route("/verify/{token}", name="verify_email")
     */
    public function verifyUserEmail($token ,JWTService $jwt,UserRepository $userRepository, EntityManagerInterface $em): Response
    {
        //On vérifie si le token est valide, n'a pas expiré et n'a pas été modifié
        if($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->check($token, $this->getParameter('app.jwtsecret'))){
            // On récupère le payload
            $payload = $jwt->getPayload($token);

            // On récupère le user du token
            $user = $userRepository->find($payload['user_id']);

            //On vérifie que l'utilisateur existe et n'a pas encore activé son compte
            if($user && !$user->getIsVerified()){
                $user->setIsVerified(true);
                $em->flush($user);
                $this->addFlash('success', 'Utilisateur activé');
                return $this->redirectToRoute('app_index');
            }
        }
        // Ici un problème se pose dans le token
        $this->addFlash('danger', 'Le token est invalide ou a expiré');
        return $this->redirectToRoute('app_login');
    }
//   /**
//      * @Route("/resendverif", name="resend_verif")
//      */
  
//     public function resendVerif(JWTService $jwt, MailerInterface $mailer, UserRepository $userRepository): Response
//     {
//         $user = $this->getUser();

//         if(!$user){
//             $this->addFlash('danger', 'Vous devez être connecté pour accéder à cette page');
//             return $this->redirectToRoute('app_login');    
//         }

//         if($user->getIsVerified()){
//             $this->addFlash('warning', 'Cet utilisateur est déjà activé');
//             return $this->redirectToRoute('account');    
//         }

//         // On génère le JWT de l'utilisateur
//         // On crée le Header
//         $header = [
//             'typ' => 'JWT',
//             'alg' => 'HS256'
//         ];

//         // On crée le Payload
//         $payload = [
//             'user_id' => $user->getId()
//         ];

//         // On génère le token
//         $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));
//         $userMail = $user->getEmail();
//         // On envoie un mail
//         $email = (new TemplatedEmail())//instancier un nouveau mail ù00
        
//         ->from('info@francoarabophone.fr')
//         ->to($userMail)
//         ->subject('Activer votre compte')
//         ->htmlTemplate('emails/registration.html.twig')//Indique le modèle Twig utilisé pour le contenu de l'e-mail
//         ->context([   //Fournit des données à utiliser dans le modèle Twig
//           'user' => $user->getNickname(),
//           'token' => $token
//       ]);
         

//       $mailer->send($email);
     
//         $this->addFlash('success', 'Email de vérification a été envoyé');
//         return $this->redirectToRoute('app_index');
//     }

     }

