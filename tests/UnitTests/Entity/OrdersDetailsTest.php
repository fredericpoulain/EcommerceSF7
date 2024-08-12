<?php

namespace App\Tests\UnitTests\Entity;

use App\Entity\Categories;
use App\Entity\ImagesProducts;
use App\Entity\Orders;
use App\Entity\OrdersDetails;
use App\Entity\Products;
use PHPUnit\Framework\TestCase;

class OrdersDetailsTest extends TestCase
{
    public function testSetQuantity()
    {
        $ordersDetails = new OrdersDetails();
        $quantity = 2;
        $ordersDetails->setQuantity($quantity);
        $this->assertEquals($quantity, $ordersDetails->getQuantity());
    }
    public function testSetPrice()
    {
        $ordersDetails = new OrdersDetails();
        $price = '1290.99';
        $ordersDetails->setPrice($price);
        $this->assertEquals($price, $ordersDetails->getPrice());
    }

    public function testSetOrder()
    {
        $ordersDetails = new OrdersDetails();
        $order = $this->createMock(Orders::class);

        $ordersDetails->setOrders($order);
        $this->assertEquals($order, $ordersDetails->getOrders());
    }

    public function testSetProduct()
    {
        $ordersDetails = new OrdersDetails();
        $product = $this->createMock(Products::class);

        $ordersDetails->setProducts($product);
        $this->assertEquals($product, $ordersDetails->getProducts());
    }



}
