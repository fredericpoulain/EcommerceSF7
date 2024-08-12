<?php

namespace App\Tests\UnitTests\Entity;

use App\Entity\BillingAddresses;
use App\Entity\Orders;
use App\Entity\OrdersDetails;
use App\Entity\ShippingAddresses;
use App\Entity\Users;
use PHPUnit\Framework\TestCase;
use function Symfony\Component\Clock\now;

class OrdersTest extends TestCase
{
    /**
     * @throws \DateMalformedStringException
     */
    public function testSetReference()
    {
        $order= new Orders();
        $reference = '0101aaa';
        $order->setReference($reference);
        $this->assertEquals($reference, $order->getReference());
    }
    public function testSetOrderDate()
    {
        $order= new Orders();
        $orderDate = new \DateTimeImmutable();
        $order->setOrderDate($orderDate);
        $this->assertEquals($orderDate, $order->getOrderDate($orderDate));
    }


    public function testSetUser()
    {
        $order= new Orders();
        $user = $this->createMock(Users::class);

        $order->setUser($user);
        $this->assertEquals($user, $order->getUser());
    }
    public function testSetBillingAddress()
    {
        $order= new Orders();
        $billingAddress= $this->createMock(BillingAddresses::class);

        $order->setBillingAddress($billingAddress);
        $this->assertEquals($billingAddress, $order->getBillingAddress());
    }
    public function testSetShippingAddress()
    {
        $order= new Orders();
        $shippingAddress= $this->createMock(ShippingAddresses::class);

        $order->setShippingAddress($shippingAddress);
        $this->assertEquals($shippingAddress, $order->getShippingAddress());
    }

    public function testAddRemoveOrdersDetails()
    {
        $order= new Orders();
        $ordersDetails = $this->createMock(OrdersDetails::class);

        $order->addOrdersDetail($ordersDetails);
        $this->assertTrue($order->getOrdersDetails()->contains($ordersDetails));

        $order->removeOrdersDetail($ordersDetails);
        $this->assertFalse($order->getOrdersDetails()->contains($ordersDetails));
    }
}
