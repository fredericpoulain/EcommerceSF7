<?php

namespace App\Tests\UnitTests\Entity;

use App\Entity\Orders;
use App\Entity\BillingAddresses;
use App\Entity\Users;
use PHPUnit\Framework\TestCase;

class BillingAddressesTest extends TestCase
{
    public function testSetAddress()
    {
        $billingAddress= new BillingAddresses();
        $address = '22 rue du code';

        $billingAddress->setAddress($address);

        $this->assertEquals($address, $billingAddress->getAddress());
    }
    public function testSetZipcode()
    {
        $billingAddress= new BillingAddresses();
        $zipcode = '59000';

        $billingAddress->setZipcode($zipcode);

        $this->assertEquals($zipcode, $billingAddress->getZipcode());
    }

    public function testSetCity()
    {
        $billingAddress= new BillingAddresses();
        $city = 'Lille';

        $billingAddress->setCity($city);

        $this->assertEquals($city, $billingAddress->getCity());
    }
    public function testSetFirstname()
    {
        $billingAddress= new BillingAddresses();
        $firstname = 'John';

        $billingAddress->setFirstname($firstname);

        $this->assertEquals($firstname, $billingAddress->getFirstname());
    }
    public function testSetLastname()
    {
        $billingAddress= new BillingAddresses();
        $lastname = 'John';

        $billingAddress->setLastname($lastname);

        $this->assertEquals($lastname, $billingAddress->getLastname());
    }
    public function testSetPhone()
    {
        $billingAddress= new BillingAddresses();
        $phone = '0601010101';

        $billingAddress->setPhone($phone);

        $this->assertEquals($phone, $billingAddress->getPhone());
    }
    public function testSetIsMain()
    {
        $billingAddress= new BillingAddresses();

        $billingAddress->setIsMain(true);

        $this->assertTrue($billingAddress->getIsMain());
    }
    public function testSetUser()
    {
        $billingAddress= new BillingAddresses();
        $user = $this->createMock(Users::class);

        $billingAddress->setUser($user);
        $this->assertEquals($user, $billingAddress->getUser());
    }
    public function testAddRemoveOrder()
    {
        $billingAddress= new BillingAddresses();
        $order = $this->createMock(Orders::class);

        $billingAddress->addOrder($order);
        $this->assertTrue($billingAddress->getOrders()->contains($order));

        $billingAddress->removeOrder($order);
        $this->assertFalse($billingAddress->getOrders()->contains($order));
    }
}
