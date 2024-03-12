<?php

namespace App\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class RegistrationControllerTest extends WebTestCase
{
    public function testRegistrationProcess(): void
    {
        //Création du client HTTP
        $client = static::createClient();

        //Requête GET vers la page d'inscription :
        $crawler = $client->request('GET', '/register');

        // Vérification de la présence du formulaire d'inscription 
        $this->assertEquals(1, $crawler->filter('h2:contains("INSCRIPTION")')->count());

        // Remplissage du formulaire avec des données valides 
        $form = $crawler->filter('form[name="registration"]')->form();
        $form['registration[nickname]'] = "test";
        $form['registration[email]'] = 't71168702@gmail.com';
        $form['registration[age]'] = 30;
        $form['registration[password][first]'] = 'testpasswordA@1';
        $form['registration[password][second]'] = 'testpasswordA@1';
        $form['registration[agreepolitique]'] = 1;
        $form['registration[acceptedTerms]'] = 1;

        //Soumission du formulaire 
        $client->submit($form);

        // Vérification de la redirection réussie 
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        // Suivi de la redirection 
        $crawler = $client->followRedirect();
      

        // Vérification de la route après l'inscription réussie
        $this->assertRouteSame('app_login');

    }
}
