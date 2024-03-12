<?php

namespace App\Tests\Functional\Controller;

use App\Entity\User;
use App\Entity\Comments;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CommentControllerTest extends WebTestCase
{

    //ce test pour tester l'ajout de commentaire avec un utilisateur vérifié
    public function testAddCommentWithverifiedUser(): void
    {

        $client = static::createClient();
        // Récupère l'EntityManager et le UserRepository pour gérer les utilisateurs
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $userRepository = $em->getRepository(User::class);
        // Récupère un utilisateur quelconque
       $user = $userRepository->findOneBy([]);
       $user->setIsVerified(true);
       $em->flush();
       // Simule la connexion de l'utilisateur
        $client->loginUser($user); 
        // Effectuer une requête vers la route /comments/add
        $client->request('GET', '/comments/add');
        // Vérifier que la requête a réussi (code HTTP 200)
        $this->assertResponseIsSuccessful();
        // Simuler les données du formulaire
        $formData = [
            'comments' => ['content' => 'Contenu du commentaire de test 1',],];
        // Soumettre le formulaire avec les données simulées
        $client->submitForm('Envoyer', $formData);
        // Vérifier que la redirection a eu lieu après la soumission du formulaire
        $this->assertResponseRedirects('/comments'); 
        // Vérifier que le commentaire a été correctement créé dans la base de données
          $commentsRepository = $em->getRepository(Comments::class);
          $comment =  $commentsRepository->findOneBy(['content' => 'Contenu du commentaire de test']);
          // Assurez-vous que le commentaire existe dans la base de données
        $this->assertNotNull($comment);
        // récupérez le commentaire depuis la base de données (par exemple, avec le Repository)
        $this->assertInstanceOf(\App\Entity\Comments::class, $comment);
        $this->assertEquals('Contenu du commentaire de test', $comment->getContent());

    }


//test l'ajout de commentaire en cas d'un utilisateur nonvérifié
    public function testAddCommentWithUnverifiedUser(): void
    {
    $client = static::createClient();

    // Récupère l'EntityManager et le UserRepository pour gérer les utilisateurs
    $em = $client->getContainer()->get('doctrine.orm.entity_manager');
    $userRepository = $em->getRepository(User::class);
   
    // Récupère un utilisateur quelconque
    $user = $userRepository->findOneBy([]);
    
    // Update the user's verification status to false
    $user->setIsVerified(false);
    $em->flush();

    // Simule la connexion de l'utilisateur
    $client->loginUser($user); 

    // Effectuer une requête vers la route /comments/add
    $client->request('GET', '/comments/add');

    // Vérifier que la redirection vers 'nonvalide' a eu lieu
    $this->assertResponseRedirects('/compte-non-valide');
}
}
