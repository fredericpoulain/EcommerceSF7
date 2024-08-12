<?php

namespace App\Tests\UnitTests\Entity;

use App\Entity\Categories;
use App\Entity\ImagesProducts;
use App\Entity\OrdersDetails;
use App\Entity\Products;
use PHPUnit\Framework\TestCase;

class ImagesTest extends TestCase
{
    public function testSetName()
    {
        $image = new ImagesProducts();
        $name = 'product-image.img';
        $image->setName($name);
        $this->assertEquals($name, $image->getName());
    }

    public function testSetCategory()
    {
        $image = new ImagesProducts();
        $product = $this->createMock(Products::class);

        $image->setProduct($product);
        $this->assertEquals($product, $image->getProduct());
    }



}
