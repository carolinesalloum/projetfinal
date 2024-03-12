<?php

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class userEntityTest extends TestCase
{
    /**
     * 
     * //vérifie si les valeurs retournées par les méthodes getters de  classe User 
     * //correspondent aux valeurs définies par les méthodes setters et retourne true
     *
     * @return void
     */
    public function testIsTrue(): void
    {
        //instancier un nouvel objet User
        $user = new User();
        //définir des valeurs pour les propriétés email, nickname, password, et age.
        $user->setEmail("user@test.com");
        $user->setNickname("Prénom");
        $user->setPassword("Monsupermotdepasse");
        $user->setAge("20");
        $user->setIsVerified('false');
    
       // Vérifie si la méthode getEmail renvoie la valeur définie avec setEmail.
        $this->assertEquals("user@test.com", $user->getEmail());
        // Vérifie si la méthode getNickname renvoie la valeur définie avec setNickname.
        $this->assertEquals("Prénom", $user->getNickname());
        // Vérifie si la méthode getPassword renvoie la valeur définie avec setPassword.
        $this->assertEquals("Monsupermotdepasse", $user->getPassword());
        //Vérifie si la méthode getAge renvoie la valeur définie avec setAge
        $this->assertEquals("20", $user->getAge());
        //Vérifie si la méthode getIsVerified renvoie la valeur définie avec setIsVerified.
        $this->assertEquals("false", $user->getIsVerified());

    }

    /**
     * Test si le getter() est égale au setter() et retourne false
     *
     * @return void
     */
    public function testIsFalse(): void
    {
        //instancier un nouvel objet User
        $user = new User();
        ////définir des valeurs pour les propriétés email, nickname, password, et age.
        $user->setEmail("user@test.com");
        $user->setNickname("Prénom");
        $user->setPassword("Monsupermotdepasse");
        $user->setAge("20");
        
        /// Vérifie que la méthode getter ne renvoie pas la valeur définie avec setter.
        $this->assertFalse($user->getEmail() === "claude-françois@test.com");
        $this->assertFalse($user->getNickname() === "Claude");
        $this->assertFalse($user->getPassword() === "Mot de passe");
        $this->assertFalse($user->getAge() === "40");
   
    }

    /**
     * Test si le getter() est vide
     *
     * @return void
     */
    public function testIsEmpty(): void
    {
        ////instancier un nouvel objet User
        $user = new User();
        //vérifier que la valeur de email ,nickname,password et age sont vide
        $this->assertEmpty($user->getEmail());
        $this->assertEmpty($user->getNickname());
        $this->assertEmpty($user->getPassword());
        $this->assertEmpty($user->getAge());
      
    }
    
    
}
