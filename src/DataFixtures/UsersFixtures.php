<?php

namespace App\DataFixtures;

use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UsersFixtures extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $hasher)
    {
    }
    public function load(ObjectManager $manager): void
    {
        $admin = new Users();
        $admin->setEmail('admin@monsite.com');
        $admin->setPassword($this->hasher->hashPassword($admin, 'azazaz'));
        $admin->setFirstname('Frédéric');
        $admin->setLastname('Poulain');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setIsVerified(true);
        $manager->persist($admin);

        $demo = new Users();
        $demo->setEmail('demo@fredericpoulain.fr');
        $demo->setPassword($this->hasher->hashPassword($admin, 'demo'));
        $demo->setFirstname('Demo');
        $demo->setLastname('Demo');
        $demo->setIsVerified(true);
        $this->addReference('user-demo', $demo);
        $manager->persist($demo);

        for ($i = 0; $i < 10; $i++) {
            $user = new Users();
            $faker = Factory::create('fr_FR');
            $user->setEmail($faker->email());
            $user->setPassword($this->hasher->hashPassword($user, 'azazaz'));
            $user->setFirstname($faker->firstName());
            $user->setLastname($faker->lastName());
            $user->setIsVerified(rand(0,1));
            //On "mémorise" ce User par une référence unique (user-0, user-1...), qui nous servira dans AddressFixture
            $this->addReference('user-' . $i, $user);

            $manager->persist($user);
        }

        $manager->flush();
    }
}
