<?php

namespace App\Controller;

use App\Entity\User;
use App\Security\EmailVerifier;
use Symfony\Component\Mime\Email;
use App\Form\RegistrationType;
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
    public function registerUser(Request $request, CategoryRepository $categoryRepository, EntityManagerInterface $manager, UserPasswordHasherInterface $passHasher, MailerInterface $mailer): Response

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
            // mailer
                $userMail = $user->getEmail();
              $email = (new TemplatedEmail())
              ->from('alsalloumcaroline@gmail.com')
              ->to($userMail)
              ->subject('Binvenue dans notre site!')
              ->htmlTemplate('emails/reset_pass.html.twig')
              ->context([
                'user' => $user->getNickname(),
            ]);
               
    
            $mailer->send($email);
          
            //Création du flashbag
            $this->addFlash('success', 'Félicitation, vous êtes inscrit, connectez vous à présent');
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
     * @Route("/verify/email", name="app_verify_email")
     */
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('register');
    }
}
