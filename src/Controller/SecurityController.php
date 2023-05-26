<?php

// use App\Entity\User;
// use App\Entity\Admin;
// use App\Entity\Category;
// use App\Form\RegistrationType;
// use Doctrine\Persistence\ManagerRegistry;
// use Symfony\Component\HttpFoundation\Request;
// use Symfony\Component\Form\Extension\Core\Type\TextType;
// use Symfony\Component\Form\Extension\Core\Type\SubmitType;
// use Symfony\Component\Form\Extension\Core\Type\PasswordType;
// use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
// use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
// use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
// use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
// use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

// namespace App\Controller;

// use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
// use Symfony\Component\HttpFoundation\Response;
// use Symfony\Component\Routing\Annotation\Route;
// use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

// class SecurityController extends AbstractController
// {


namespace App\Controller;


use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;


class SecurityController extends AbstractController
{
   

    /**
     *
     *
     * @Route("/login", name="app_login")
     */

    public function login(AuthenticationUtils $authenticationUtils, CategoryRepository $categoryRepository): Response
    { //la condition nous permet de savoire si un utilisateur est connecté ou pas
        //  if ($this->getUser()) {
        //      return $this->redirectToRoute('target_path');
        //  }
        $categories = $categoryRepository->findAll();

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        //  pour récupérer le dernier nom qui été utilisé par l'utilisateur
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render(
            'security/login.html.twig',
            [
                'last_username' => $lastUsername,
                'error' => $error,
                'categories' => $categories,

            ]
        );
    }



    /**
     *
     *
     * @Route("/logout", name="app_logout")
     */

    //cette méthode peut rester vide ,il est gérer automatiquement par symfony
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
