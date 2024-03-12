<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class SecurityControllerTest extends WebTestCase
{
    /**
     * Teste l'affichage de la page de connexion.
     *
     * @return void
     */
    public function testDisplayLogin(): void 
    {
        $client = static::createClient();
         // Demande la page de connexion
        $client->request("GET", "/login");
        // Affirme que le code de statut de la réponse est HTTP_OK
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
         // Affirme que le contenu HTML contient un élément <h2> avec le texte "CONNECTEZ-VOUS"
        $this->assertSelectorTextContains("h2", "CONNECTEZ-VOUS");
        // Affirme qu'il n'y a pas d'élément avec la classe "alert alert-danger"
        $this->assertSelectorNotExists("alert alert-danger");
    }

    /**
     * Test la connexion utilisateur en cas d'erreur
     *
     * @return void
     */
    public function testConnexionAvecMauvaisIdentifiants(): void 
    {
        $client = static::createClient();
        // Demande la page de connexion
        $crawler = $client->request("GET", "/login");
        var_dump($crawler->html());
        // Vérifie que la page de connexion est accessible
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        // Remplit le formulaire avec des identifiants incorrects
        $form = $crawler->selectButton("Me connecter")->form([
            '_username' => 'user@test.com',
            '_password' => 'fakepassword',
           
        ]);

        $client->submit($form);
        // Vérifie la redirection après la soumission du formulaire
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        // Suivre la redirection
        $client->followRedirect();
    }

//      /**
//       * Test la connexion utilisateur en cas de succés
//      *
//       * @return void
//       */
//      public function testSuccessfulLogin(): void 
//      {
//          $client = static::createClient();
//         $userRepository = static::getContainer()->get(UserRepository::class);

//           //Récupérer l'utilisateur de test
//         $testUser = $userRepository->findOneByEmail('carolin.salloum@gmail.com');

//        // Simuler la connexion de l'utilisateur de test
//          $client->loginUser($testUser);
// // Envoyer une requête GET à la page d'accueil après la connexion
//          $client->request('GET', '/');
//           // Vérifier que la réponse est réussie (code HTTP 200)
//          $this->assertResponseIsSuccessful();
//          // Vérifier que le contenu de la balise h2 contient le nom de l'utilisateur
//         $this->assertSelectorTextContains('h2', 'Hello');
//     }
}