<?php




namespace App\Controller;


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
    public function login(AuthenticationUtils $authenticationUtils): Response
    { //la condition nous permet de savoire si un utilisateur est connecté ou pas
        //  if ($this->getUser()) {
        //      return $this->redirectToRoute('target_path');
        //  }
        
       

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        //  pour récupérer le dernier nom qui été utilisé par l'utilisateur
         $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render(
            'security/login.html.twig',
            [
                 'last_username' => $lastUsername,
                'error' => $error,
                

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
