<?php

namespace App\Tests\UnitTests\Entity;

use App\Entity\Categories;
use App\Entity\Products;
use PHPUnit\Framework\TestCase;

class CategoriesTest extends TestCase
{
    public function testSetName()
    {
        $category = new Categories();
        $name = 'Ordinateur';

        $category->setName($name);

        $this->assertEquals($name, $category->getName());
    }
    public function testSetDescription()
    {
        $category = new Categories();
        $description = 'Catégorie des ordinateurs portable, tour...';

        $category->setDescription($description);

        $this->assertEquals($description, $category->getDescription());
    }

    public function testSetSlug()
    {
        $category = new Categories();
        $slug = 'ordinateur';
        $category->setSlug($slug);
        $this->assertEquals($slug, $category->getSlug());
    }


    public function testSetCatParent()
    {
        $category = new Categories();
        $parent = $this->createMock(Categories::class);

        $category->setCatParent($parent);
        $this->assertEquals($parent, $category->getCatParent());
    }

    public function testAddRemoveCategory()
    {
        $category= new Categories();
        $cat = $this->createMock(Categories::class);

        $category->addCategory($cat);
        $this->assertTrue($category->getCategories()->contains($cat));

        $category->removeCategory($cat);
        $this->assertFalse($category->getCategories()->contains($cat));
    }
    public function testAddRemoveProducts()
    {
        $category= new Categories();
        $product = $this->createMock(Products::class);

        $category->addProduct($product);
        $this->assertTrue($category->getProducts()->contains($product));

        $category->removeProduct($product);
        $this->assertFalse($category->getProducts()->contains($product));
    }
}
