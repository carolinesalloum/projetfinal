<?php

namespace App\Tests\Unit;

use App\Entity\Type;
use App\Entity\Level;
use App\Entity\Product;
use App\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class productEntityTest extends KernelTestCase
{ 
    //Cette méthode crée une instance de l'entité Product avec des valeurs 
    //fictives pour les propriétés title, category, level, et type.
public function getEntity(): Product
{
   //on crée une nouvel entity Product
    $product=new Product;
//les instances de Category, Level, et Type sont également créées dans cette méthode 
//car ils sont des entitys et ils ont relations avec l'entity Product
    $category = new Category;
    $level= new Level();
    $type = new Type();

    $product
    ->setTitle('Tiltle #1')
    ->setCategory($category)
     ->setLevel($level)
     ->setType($type);
    return $product;
}
// Cette méthode vérifier si une instance de l'entité Product est valide
public function testEntityIsValid() : void
 {
    //démarre le noyau de Symfony
 self::bootKernel();
 //récupère le conteneur de services de Symfony.
$container = static::getContainer();
//créer une instance de l'entité Product
$product= $this->getEntity();
// utiliser le service de validation pour valider l'entité Product.
//Les erreurs de validation (le cas échéant) seront stockées dans la variable $errors.
 $errors = $container->get('validator')->validate($product);
 //vérifier que le nombre d'erreurs de validation est égal à zéro
$this->assertCount(0,$errors);

}
//Cette méthode teste si une instance de l'entité Product avec un titre vide ne passe pas la validation
public function testInvalidTitle(){
  //démarre le noyau de Symfony
  $kernel=self::bootKernel();
   //récupère le conteneur de services de Symfony.
    $container = static::getContainer();
   //créer une instance de l'entité Product
    $product = $this->getEntity();
    //donner une valeur vide au title
  $product->setTitle('');
  //récupèrer les erreurs
   $errors = $container->get('validator')->validate($product);
////vérifier que le nombre d'erreurs de validation est égal à 1 car le titre ne peut pas être vide
   $this->assertCount(1,$errors);
}


}

