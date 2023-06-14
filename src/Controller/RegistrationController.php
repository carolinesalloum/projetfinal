<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\JWTService;
use App\Form\RegistrationType;
use App\Security\EmailVerifier;
use Symfony\Component\Mime\Email;
use App\Repository\UserRepository;
use Symfony\Component\Mime\Address;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    /**
     *
     *
     * @Route("/admin/register", name="admin_register")
     */
    public function registerAdmin(Request $request,EntityManagerInterface $manager, UserPasswordHasherInterface $passHasher,CategoryRepository $categoryRepository): Response
    {
        //Cette méthode permet la création d'un compte utilisateur avec des privilèges Administrateur
        //Nous récupérons la liste des Catégories pour notre navbar
        $categories = $categoryRepository->findAll();
        //Nous créons et renseignons notre Entity User
        $user = new User;
        //Nous appele la formulaire  pour l'inscription
        $userForm = $this->createForm(RegistrationType::class, $user);
        
        //Nous traitons les données reçues au sein de notre formulaire
        $userForm->handleRequest($request);
        if ($request->isMethod('post') && $userForm->isValid()) {
            //On récupère les informations du formulaire
            $data = $userForm->getData();
            $user->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
            $user->setNickname($userForm->get('nickname')->getData());
            $user->setEmail($userForm->get('email')->getData());
            $user->setPassword($passHasher->hashPassword($user, $data['password']));
            $manager->persist($user);
            $manager->flush();
            return $this->redirect($this->generateUrl('app_login'));
        }
        //Si le formulaire n'est pas validé, nous le présentons à l'utilisateur
        return $this->render('register/register.html.twig', [
            'categories' => $categories,
            'formName' => 'Inscription Utilisateur',
            'dataForm' => $userForm->createView(),
            // 'category' => $category
        ]);
    }


    /**
     * @Route("/register", name="register")
     */
    public function registerUser(Request $request, CategoryRepository $categoryRepository, EntityManagerInterface $manager, UserPasswordHasherInterface $passHasher, MailerInterface $mailer,JWTService $jwt): Response

    {
        //Cette méthode permet la création d'un compte Client via formulaire
        //Nous récupérons la liste des Catégories pour notre navbar
        $categories = $categoryRepository->findAll();
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
        $this->getParameter('app.jwtsecret'));// on passe le secret avec $this->getParameter('app.jwtsecret')

        //dd($token);
            // mailer
                $userMail = $user->getEmail();
              $email = (new TemplatedEmail())
              ->from('alsalloumcaroline@gmail.com')
              ->to($userMail)
              ->subject('Activer votre compte')
              ->htmlTemplate('emails/registration.html.twig')
              ->context([
                'user' => $user->getNickname(),
                'token' => $token
            ]);
               
    
            $mailer->send($email);
          
            //Création du flashbag
            $this->addFlash('danger', 'Votre compte n\'est pas encore activé');
            //Après le transfert de notre Entity User, on retourne sur le login
            return $this->redirectToRoute('app_login');
        }
        //Si notre formulaire n'est pas validé, nous le présentons à l'Utilisateur
        return $this->render('register/register.html.twig', [
            'formName' => 'Inscription Utilisateur',
            'userForm' => $userForm->createView(),
            'categories' => $categories,
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
// /**
//      * @Route("/renvoiverif", name="resend_verif")
//      */
//     public function resendVerif(JWTService $jwt, MailerInterface $mail, UserRepository $userRepository): Response
//     {
//         $user = $this->getUser();

//         if(!$user){
//             $this->addFlash('danger', 'Vous devez être connecté pour accéder à cette page');
//             return $this->redirectToRoute('app_login');    
//         }

//         if($user->getIsVerified()){
//             $this->addFlash('warning', 'Cet utilisateur est déjà activé');
//             return $this->redirectToRoute('app_index');    
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

//         // On envoie un mail
//         $userMail = $user->getEmail();
//               $email = (new TemplatedEmail())
//               ->from('alsalloumcaroline@gmail.com')
//               ->to($userMail)
//               ->subject('Activer votre compte')
//               ->htmlTemplate('emails/registration.html.twig')
//               ->context([
//                 'user' => $user->getNickname(),
//                 'token' => $token
//             ]);
//         $mail->send($email);
//         $this->addFlash('success', 'Email de vérification envoyé');
//         return $this->redirectToRoute('profile_index');
//     }



    }

