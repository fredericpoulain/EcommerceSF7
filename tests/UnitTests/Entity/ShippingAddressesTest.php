<?php

namespace App\Tests\UnitTests\Entity;

use App\Entity\Orders;
use App\Entity\ShippingAddresses;
use App\Entity\Users;
use PHPUnit\Framework\TestCase;

class ShippingAddressesTest extends TestCase
{
    public function testSetAddress()
    {
        $shippingAddress= new ShippingAddresses();
        $address = '22 rue du code';

        $shippingAddress->setAddress($address);

        $this->assertEquals($address, $shippingAddress->getAddress());
    }
    public function testSetZipcode()
    {
        $shippingAddress= new ShippingAddresses();
        $zipcode = '59000';

        $shippingAddress->setZipcode($zipcode);

        $this->assertEquals($zipcode, $shippingAddress->getZipcode());
    }

    public function testSetCity()
    {
        $shippingAddress= new ShippingAddresses();
        $city = 'Lille';

        $shippingAddress->setCity($city);

        $this->assertEquals($city, $shippingAddress->getCity());
    }
    public function testSetFirstname()
    {
        $shippingAddress= new ShippingAddresses();
        $firstname = 'John';

        $shippingAddress->setFirstname($firstname);

        $this->assertEquals($firstname, $shippingAddress->getFirstname());
    }
    public function testSetLastname()
    {
        $shippingAddress= new ShippingAddresses();
        $lastname = 'John';

        $shippingAddress->setLastname($lastname);

        $this->assertEquals($lastname, $shippingAddress->getLastname());
    }
    public function testSetPhone()
    {
        $shippingAddress= new ShippingAddresses();
        $phone = '0601010101';

        $shippingAddress->setPhone($phone);

        $this->assertEquals($phone, $shippingAddress->getPhone());
    }
    public function testSetIsMain()
    {
        $shippingAddress= new ShippingAddresses();

        $shippingAddress->setIsMain(true);

        $this->assertTrue($shippingAddress->getIsMain());
    }
    public function testSetUser()
    {
        $shippingAddress= new ShippingAddresses();
        $user = $this->createMock(Users::class);

        $shippingAddress->setUser($user);
        $this->assertEquals($user, $shippingAddress->getUser());
    }
    public function testAddRemoveOrder()
    {
        $shippingAddress= new ShippingAddresses();
        $order = $this->createMock(Orders::class);

        $shippingAddress->addOrder($order);
        $this->assertTrue($shippingAddress->getOrders()->contains($order));

        $shippingAddress->removeOrder($order);
        $this->assertFalse($shippingAddress->getOrders()->contains($order));
    }
}
