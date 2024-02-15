<?php

//Ce fichier fait partie du système de sécurité de Symfony,il est chargée d'authentifier les utilisateurs 
//en fonction de leurs informations d'identification, telles qu'un nom d'utilisateur et un mot de passe.



namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginAuthentificator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;//TargetPathTrait est un outil pratique pour gérer les chemins cibles lors de l'authentification dans Symfony.

    public const LOGIN_ROUTE = 'app_login';//Définit une constante LOGIN_ROUTE égale à 'app_login'. Cette constante représente le nom de la route à utiliser pour l'affichage du formulaire de connexion.

    private UrlGeneratorInterface $urlGenerator; //UrlGeneratorInterface type d'interface dans Symfony qui est utilisé pour générer des URLs

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator; // 
    }

    //En résumé, la méthode authenticate extrait l'email et le mot de passe de la requête, enregistre l'email dans la session, et crée un objet Passport qui encapsule ces informations d'identification, y compris un jeton CSRF. Ce Passport sera ensuite utilisé pour tenter l'authentification de l'utilisateur.
    public function authenticate(Request $request): Passport
    { //Cette méthode est appelée lorsqu'une tentative d'authentification est effectuée
        $email = $request->request->get('email', '');// Récupération de l'Email 

        $request->getSession()->set(Security::LAST_USERNAME, $email);//L'email est enregistré dans la session Symfony avec la clé


        //La méthode retourne un objet Passport, qui représente les informations d'identification de l'utilisateur.
        return new Passport(   
            new UserBadge($email),//Le UserBadge est responsable de récupérer l'utilisateur associé à l'email.
            new PasswordCredentials($request->request->get('password', '')),//Crée un badge PasswordCredentials avec le mot de passe fourni dans le champ de formulaire 'password'.
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),//Ajoute un badge CsrfTokenBadge avec le jeton CSRF (Cross-Site Request Forgery) extrait du champ de formulaire '_csrf_token'. Cela ajoute une protection CSRF lors de la soumission du formulaire.
            ]
        ); 
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {//a méthode onAuthenticationSuccess gère la redirection après une authentification réussie. Elle vérifie si le compte de l'utilisateur est activé (commenté), puis tente de rediriger l'utilisateur vers le chemin cible s'il en existe un dans la session. Si aucun chemin cible n'est trouvé, elle redirige l'utilisateur vers la page d'accueil de l'application. La méthode getLoginUrl retourne l'URL de la page de connexion en cas d'échec de l'authentification.

        //  // Check if the user's account is activated
        //  $user = $this->getUser();
        //  if (!$user->getIsActivated()) {
        //      // Redirect to an inactive account page or show an error message
        //      return new RedirectResponse($this->urlGenerator->generate('inactive_account'));
        //  }

    //    Redirection vers le Chemin Cible (Target Path) :récupérer le chemin cible (target path) enregistré dans la session.
// Si un chemin cible existe, la méthode retourne une nouvelle RedirectResponse vers ce chemin cible.
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        // For example:
        // return new RedirectResponse($this->urlGenerator->generate('some_route'));
        //throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);
        return new RedirectResponse($this->urlGenerator->generate('app_index'));//Redirection par Défaut (si pas de chemin cible) :
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
