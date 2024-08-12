<?php

namespace App\DataFixtures;


use App\Entity\ImagesProducts;
use App\Service\NumberProductsFixtures;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


class ImagesProductsFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(private readonly NumberProductsFixtures $numberProductsFixtures)
    {
    }
    public function load(ObjectManager $manager,): void
    {
        $numberProducts = $this->numberProductsFixtures->getNumber();
        for ($i=1; $i<$numberProducts; $i++) {
            for ($j=1; $j<=3; $j++ ){
                $image = new ImagesProducts();
                $image->setName('product-thumb-'. $i . '-' . $j .'.png');
                $image->setProduct($this->getReference('prod-' . $i));
                $manager->persist($image);
            }

        }
        $manager->flush();
    }
    public function getDependencies(): array
    {
        return [
            ProductsFixture::class
        ];
    }
}
