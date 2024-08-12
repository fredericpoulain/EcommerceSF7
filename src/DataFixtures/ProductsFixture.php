<?php

namespace App\DataFixtures;

use App\Entity\Categories;
use App\Entity\Product;
use App\Entity\Products;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Yaml\Yaml;

class ProductsFixture extends Fixture
{
    private int $compteur = 1;
    private int $compteurProduct = 1;

    public function __construct(private readonly SluggerInterface $slugger, private readonly ParameterBagInterface $parameterBag,)
    {
    }
    public function load(ObjectManager $manager): void
    {
        //https://symfony.com/doc/current/components/yaml.html
        $yamlFilePath = $this->parameterBag->get('productsFixtures');
        $allProducts = Yaml::parse(file_get_contents($yamlFilePath));
        foreach ($allProducts as $category => $products) {
            foreach ($products as $product) {
                $prod = new Products();
                $prod->setName($product['nom']);
                $prod->setDescription($product['description']);
                $prod->setPrice($product['prix']);
                $prod->setStock($product['stock']);
                $this->addReference('prod-' . $this->compteurProduct, $prod);
                $prod->setSlug($this->slugger->slug($prod->getName())->lower());

                //On va chercher une référence de catégorie
                $parentCategory = $this->getReference('cat-' . $this->compteur);
                $prod->setCategory($parentCategory);

                $manager->persist($prod);
                $this->compteurProduct++;
            }
            $this->compteur++;
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            Categories::class
        ];
    }
}


