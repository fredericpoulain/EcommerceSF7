<?php

namespace App\DataFixtures;


use App\Entity\BillingAddresses;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class BillingAddressFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        //Demo user :
        $this->createAddress($manager, true, 'demo');
        for ($j=0; $j< mt_rand(0,3); $j++){
            $this->createAddress($manager, false, 'demo');
        }

        //je boucle 10x pour les 10 users de UsersFixtures
        for ($i=0; $i<10; $i++){

            //Création d'une adresse principale
            $this->createAddress($manager, true, $i);

            //Et aléatoirement, aucune ou jusqu'à trois adresses secondaires
            for ($j=0; $j< mt_rand(0,3); $j++){
                $this->createAddress($manager, false, $i);
            }
        }

        $manager->flush();
    }

    private function createAddress(ObjectManager $manager, $main, $i ): void
    {
        $address = new BillingAddresses();
        $faker = Factory::create('fr_FR');
        $address->setAddress($faker->streetAddress());
        $address->setFirstname($faker->firstName());
        $address->setLastname($faker->lastName());
        //De manière aléatoire, le code postal possède un espace en trop (exemple : 59 500, au lieu de 59500)
        //Une solution possible est donc de remplacer les espaces par 'rien du tout' sur le postcode généré par le faker
        $address->setZipcode(str_replace(' ', '', $faker->postcode));
        $address->setCity($faker->city());
        $address->setPhone($faker->numerify('06########'));
        $address->setIsMain($main);
        //on récupère un user, sous la référence de type "user-0" ou "user-1" (en fonction de $i). Voir code de UsersFixtures
        $address->setUser($this->getReference('user-'.$i));
        $manager->persist($address);
    }
    public function getDependencies(): array
    {
        return [
            UsersFixtures::class
        ];
    }
}
