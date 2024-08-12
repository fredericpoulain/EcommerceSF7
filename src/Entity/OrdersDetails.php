<?php

namespace App\Entity;

use App\Repository\OrdersDetailsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrdersDetailsRepository::class)]
class OrdersDetails
{


    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\Column]
    private ?float $price = null;


    /**
     * Ne pas oublier que cette entité fait le lien entre "Products" et "Orders".
     * On peut donc la considérer comme étant la table supplémentaire créée avec un principe de ManyToMany entre "Products" et "Orders"...
     * Pour que les clés étrangères orders et products soient clés primaires et clés étrangères à la fois (clés composites)
     * alors, on va ajouter "#[ORM\Id]" pour orders et Products après avoir supprimé l'id (et le getter) généré automatiquement par doctrine.
     * SOURCE : https://stackoverflow.com/questions/15616157/doctrine-2-and-many-to-many-link-table-with-an-extra-field/15630665#15630665
     */
    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'ordersDetails')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Orders $orders = null;

    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'ordersDetails')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Products $products = null;



    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getOrders(): ?Orders
    {
        return $this->orders;
    }

    public function setOrders(?Orders $orders): static
    {
        $this->orders = $orders;

        return $this;
    }

    public function getProducts(): ?Products
    {
        return $this->products;
    }

    public function setProducts(?Products $products): static
    {
        $this->products = $products;

        return $this;
    }
}
