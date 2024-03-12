<?php

namespace App\Tests;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
class ContactControllerTest extends WebTestCase
{
    private $testData;
    

    /**
     * REG-CONTACT-01
     * Ce test permet de tester que la page /contact existe bien avec un status 200
     * 
     * @return void OK si requête HTTP retourne le status 200
     */
    public function testExistencePageContactOK()
{
    //Elle crée une instance du client HTTP
    $client = static::createClient();
    //la page /contact nécessite une authentification, on dois simuler une connexion avant d'accéder à la page.
    // Récupère l'EntityManager et le UserRepository pour gérer les utilisateurs
    $em = $client->getContainer()->get('doctrine.orm.entity_manager');
    $userRepository = $em->getRepository(User::class);
    // Récupère un utilisateur quelconque
    $user = $userRepository->findOneBy([]);
    // Simule la connexion de l'utilisateur
    $client->loginUser($user); 
    // Effectue une requête GET à la page /contact
    $client->request('GET', '/contact');

    // Utilise les méthodes d'assertion de Symfony pour vérifier que le code de statut de la réponse est HTTP_OK (200)
    //Le code de statut HTTP 200 signifie "OK" (d'accord). C'est une réponse standard qui indique que la requête HTTP a été traitée avec succès
    $this->assertSame(
        Response::HTTP_OK,
        $client->getResponse()->getStatusCode(),
        sprintf(
            "❌ - The request to /contact should return a status code 200, got %d instead.",
            $client->getResponse()->getStatusCode()
        )
    );
}

 /**
     * REG-CONTACT-02
     * Ce test permet de vérifier que la page /contacts n'existe pas
     * URL de connexion: /contacts
     * 
     * @return void OK si requête HTTP retourne le status 404
     */
    public function testExistencePageContactsKO()
    {
        $client = static::createClient();
         // Récupère l'EntityManager et le UserRepository pour gérer les utilisateurs
   
       $client->request('GET', '/contacts');
       // Vérifie que le code de statut de la réponse est égal à 404
       $this->assertEquals(
        404,
        $client->getResponse()->getStatusCode(),
        "La requête sur /contact doit retourner un statut 404"
    );
    }


 /**
     * REG-CONTACT-03
     * Ce test vérifie la présence d'un champ de formulaire avec l'attribut 
     * name égal à "contact[mail]" sur la page /contact
     *
     * 
     * @return void OK si requête HTTP retourne le status 404
     */
    public function testFormInputEmail()
    {
        
        // Récupère l'EntityManager et le UserRepository pour gérer les utilisateurs
        $client = static::createClient();
        // Récupère l'EntityManager et le UserRepository pour gérer les utilisateurs
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $userRepository = $em->getRepository(User::class);
       // Récupère un utilisateur quelconque
       $user = $userRepository->findOneBy([]);
       // Simule la connexion de l'utilisateur
        $client->loginUser($user); 
        // Effectue une requête GET à la page /contact
        $crawler = $client->request('GET', '/contact');
        // Utilisez les méthodes d'assertion de Symfony pour vérifier la présence du champ d'entrée
        $this->assertCount(1, $crawler->filter('input[name="contact[mail]"]'), "❌ - Doit disposer d'un tag input avec un attribut name avec pour contenu email");
    }

    /**
     * REG-CONTACT-04
     * Ce test permet de tester l'existence obligatoirement de deux tags "input" dans la page /contact
     * URL de connexion: /contact
     * 
     * @return void OK si dans la page /contact il y a bien quatre tags input
     */
    public function testExistenceDeuxInputOK()
    {
        $client = static::createClient();
         // Récupère l'EntityManager et le UserRepository pour gérer les utilisateurs
     
    //     // Récupère l'EntityManager et le UserRepository pour gérer les utilisateurs
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $userRepository = $em->getRepository(User::class);
    //    // Récupère un utilisateur quelconque
        $user = $userRepository->findOneBy([]);
    //    // Simule la connexion de l'utilisateur
        $client->loginUser($user); 
        $attendu = $client->request('GET', '/contact');
        $this->assertCount(
            $this->testData['testExistenceDeuxInputOK']['expectedTagCount'],
            $attendu->filter('input'),
            "❌ - Doit retourner obligatoirement deux (2) tags input dans le formulaire"
        );
    }

    /**
     * REG-CONTACT-05
     * // ce test vérifie si la page contient un bouton de type "submit"
     * URL de connexion: /contact
     * 
     * @return void OK si dans la page /contact il y a bien quatre tags input
     */

    public function testFormButtonSubmit()
    {
        // ce test vérifie si la page contient un bouton de type "submit"
        $client = WebTestCase::createClient();
    $em = $client->getContainer()->get('doctrine.orm.entity_manager');
    $userRepository = $em->getRepository(User::class);
   // Récupère un utilisateur quelconque
   $user = $userRepository->findOneBy([]);
   // Simule la connexion de l'utilisateur
    $client->loginUser($user); 
        $attendu = $client->request('GET', '/contact');
        $this->assertCount(1, $attendu->filter('button[type="submit"]'), "❌ - Doit disposer d'un button de type submit");
    }





    public function testIfSubmitContactFormIsSuccessful(): void
    {
        $client = static::createClient();

        // Simulate user login (adjust the user credentials based on your needs)
        $crawler = $client->request('GET', '/login');
        $form = $crawler->filter('form[name="login"]')->form([
            '_username' => 't71168702@gmail.com',
            '_password' => 'testpasswordA@1',
        ]);
        $client->submit($form);
          // Follow the redirect after login
        $crawler = $client->followRedirect();
        // Ensure the user is redirected after login (adjust the route based on your needs)
        $this->assertResponseRedirects('/');

        // Request the contact page
        $crawler = $client->request('GET', '/contact');

        // Check if the submit button is present
        $submitButton = $crawler->selectButton('Submit'); // Adjust the button text based on your form
        $this->assertCount(1, $submitButton);

        // Select the form and fill in the data
        $form = $submitButton->form([
            'contact[mail]' => 't71168702@gmail.com',
            'contact[content]' => 'Test message',
        ]);

        // Submit the form
        $client->submit($form);

        // Check for a successful redirection
        $this->assertResponseRedirects();

        // Check if an email has been sent (adjust the expected count based on your email sending logic)
        $this->assertEmailCount(1);

        // Follow the redirect after form submission (adjust the route based on your needs)
        $crawler = $client->followRedirect();

        // Check the final route
        $this->assertRouteSame('app_index');
    }
}


    