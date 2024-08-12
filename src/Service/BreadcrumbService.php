<?php

namespace App\Service;

class BreadcrumbService
{

    private array $data = [];

    public function getData(object $categories): array
    {
        //on initialise le fil d'Ariane par la catégorie actuelle
        $this->arrayUnshift($categories);
        //on récupère son parent
        $parent = $categories->getCatParent();
        //Si le parent existe, on entre dans la boucle :
        while ($parent !== null) {
            $this->arrayUnshift($parent);
            $parent = $parent->getCatParent();
            //La boucle continue tant qu'un parent existe...
        }
        return $this->data;
    }
    private function arrayUnshift($data): void
    {
        array_unshift($this->data,
            [
                'nom' => $data->getName(),
                'slug' => $data->getSlug(),
                //Si le parent récupère un tableau de produit
                'path' => $data->getProducts()->toArray() ? 'app_productsList' : 'app_categorys'
            ]
        );
    }

}