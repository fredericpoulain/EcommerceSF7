<?php

namespace App\Tests\UnitTests\Entity;

use App\Entity\Categories;
use App\Entity\OrdersDetails;
use App\Entity\Products;
use PHPUnit\Framework\TestCase;

class ProductsTest extends TestCase
{
    public function testSetName()
    {
        $product = new Products();
        $name = 'Acer tuf F15';
        $product->setName($name);
        $this->assertEquals($name, $product->getName());
    }
    public function testSetDescription()
    {
        $product = new Products();
        $description = "c'est un pc de gamer";
        $product->setDescription($description);
        $this->assertEquals($description, $product->getDescription());
    }

    public function testSetPrice()
    {
        $product = new Products();
        $price = '1290.99';
        $product->setPrice($price);
        $this->assertEquals($price, $product->getPrice());
    }


    public function testSetSlug()
    {
        $product = new Products();
        $slug = 'acer-tuf-f15';
        $product->setSlug($slug);
        $this->assertEquals($slug, $product->getSlug());
    }
    public function testSetStock()
    {
        $product = new Products();
        $stock = 10;
        $product->setStock($stock);
        $this->assertEquals($stock, $product->getStock());
    }
    public function testSetCategory()
    {
        $product = new Products();
        $category = $this->createMock(Categories::class);

        $product->setCategory($category);
        $this->assertEquals($category, $product->getCategory());
    }

    public function testAddRemoveOrdersDetails()
    {
        $product = new Products();
        $ordersDetails = $this->createMock(OrdersDetails::class);

        $product->addOrdersDetail($ordersDetails);
        $this->assertTrue($product->getOrdersDetails()->contains($ordersDetails));

        $product->removeOrdersDetail($ordersDetails);
        $this->assertFalse($product->getOrdersDetails()->contains($ordersDetails));
    }

}
