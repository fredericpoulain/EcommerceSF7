<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Yaml\Yaml;

readonly class NumberProductsFixtures
{


    public function __construct(private ParameterBagInterface $parameterBag)
    {
    }
    public function getNumber(): int
    {
        $yamlFilePath = $this->parameterBag->get('productsFixtures');
        $allProducts = Yaml::parse(file_get_contents($yamlFilePath));

        return array_reduce($allProducts, function ($carry, $products) {
            return $carry + count($products);
        }, 1);

    }
}