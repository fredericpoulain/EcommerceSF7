<?php

namespace App\Tests\UnitTests\Entity;


use App\Entity\BillingAddresses;
use App\Entity\Orders;
use App\Entity\ShippingAddresses;
use App\Entity\Users;
use PHPUnit\Framework\TestCase;

class UsersTest extends TestCase
{
    public function testSetEmail()
    {
        $user = new Users();
        $email = 'test@example.com';

        $user->setEmail($email);

        $this->assertEquals($email, $user->getEmail());
    }

    public function testSetRoles()
    {
        $user = new Users();
        $roles = ['ROLE_USER'];

        $user->setRoles($roles);

        $this->assertEquals($roles, $user->getRoles());
    }

    public function testSetPassword()
    {
        $user = new Users();
        $password = 'azazazaz';

        $user->setPassword($password);

        $this->assertEquals($password, $user->getPassword());
    }

    public function testSetFirstName()
    {
        $user = new Users();
        $firstname = 'John';

        $user->setFirstname($firstname);

        $this->assertEquals($firstname, $user->getFirstname());
    }

    public function testSetLastName()
    {
        $user = new Users();
        $lastname = 'Doe';

        $user->setFirstname($lastname);

        $this->assertEquals($lastname, $user->getFirstname());
    }


    public function testSetIsVerified()
    {
        $user = new Users();

        $user->setIsVerified(true);

        $this->assertTrue($user->getIsVerified());
    }

    public function testSetCreatedAt()
    {
        $user = new Users();
        $createdAt = new \DateTimeImmutable();

        $user->setCreatedAt($createdAt);

        $this->assertEquals($createdAt, $user->getCreatedAt());
    }

    // Add more test methods for other setters...

    public function testAddRemoveShippingAddress()
    {
        $user = new Users();
        $shippingAddress = $this->createMock(ShippingAddresses::class);

        $user->addShippingAddress($shippingAddress);
        $this->assertTrue($user->getShippingAddresses()->contains($shippingAddress));

        $user->removeShippingAddress($shippingAddress);
        $this->assertFalse($user->getShippingAddresses()->contains($shippingAddress));
    }
    public function testAddRemoveBillingAddress()
    {
        $user = new Users();
        $billingAddress = $this->createMock(BillingAddresses::class);

        $user->addBillingAddress($billingAddress);
        $this->assertTrue($user->getBillingAddresses()->contains($billingAddress));

        $user->removeBillingAddress($billingAddress);
        $this->assertFalse($user->getBillingAddresses()->contains($billingAddress));
    }

    public function testAddRemoveOrder()
    {
        $user = new Users();
        $order = $this->createMock(Orders::class);

        $user->addOrder($order);
        $this->assertTrue($user->getOrders()->contains($order));

        $user->removeOrder($order);
        $this->assertFalse($user->getOrders()->contains($order));
    }


}
