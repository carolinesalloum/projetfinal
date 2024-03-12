<?php

namespace App\Tests\Unit\Repository;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRepoTest extends KernelTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * Récupère le noyau Symfony ,ce methode est utilisée pour effectuer des initialisations ou des configurations nécessaires pour les tests.
     *
     * @return void
     */
    protected function setUp(): void
    {
        //Initialisation du Kernel
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    /**
     * Vérifie si le nombre d'utilisateur en base de données
     *
     * @return void
     */
    public function testCountUser(): void
    {
        $user = $this->entityManager
            ->getRepository(User::class)
            ->count([])
        ;

        $this->assertEquals(1, $user);
    }

    /**
     * Vérifie si email est bien en base de données
     *
     * @return void
     */
    public function testSearchByEmail(): void
    {
        //récupérer un utilisateur à partir de la base de données. 
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 't71168702@gmail.com'])
        ;
       //vérifier que l'utilisateur récupéré de la base de données a bien l'e-mail attendu
        $this->assertEquals("t71168702@gmail.com", $user->getEmail());
    }

    /**
     * Ferme le noyau Symfony,ce méthode est utiliser pour effectuer des nettoyages ou des actions de clôture après l'exécution d'un test
     *
     * @return void
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager->close();
        $this->entityManager = null;
    }
}
