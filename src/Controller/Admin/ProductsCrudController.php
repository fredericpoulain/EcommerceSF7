<?php

namespace App\Controller\Admin;

use App\Entity\Categories;
use App\Entity\Products;
use App\Form\ProductsImagesType;
use App\Repository\CategoriesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ProductsCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly CategoriesRepository $categoriesRepository,
//        private readonly EntityManagerInterface $entityManager

    )
    {
    }
    public static function getEntityFqcn(): string
    {
        return Products::class;
    }


    /**
     * @throws ORMException
     */
    public function configureFields(string $pageName): iterable
    {
        /*
         * Dans le champ de selection d'une catégorie, on doit sélectionner uniquement les
         * catégories de dernier niveau
         */

        $lowLevelCategoryChoices = [];
        $lowLevelCategories = $this->categoriesRepository->findLowLevelCategories();
        foreach ($lowLevelCategories as $category) {
            $lowLevelCategoryChoices[$category->getName()] = $category;
        }

        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name')->setLabel('Nom du produit'),
            TextEditorField::new('description')->setLabel('Description')->hideOnIndex(),
            MoneyField::new('price')
                ->setLabel('Prix')->setCurrency('EUR'),
            SlugField::new('slug')->setTargetFieldName('name')->hideOnIndex(),
            AssociationField::new('category')->onlyOnIndex()->setLabel(' Catégorie'),
            IntegerField::new('stock')->setLabel('Stock'),
            ChoiceField::new('category')
                ->setLabel(' Nom de la catégorie')
                ->setChoices($lowLevelCategoryChoices)
                ->hideOnIndex(),
            CollectionField::new('images')
                ->hideOnIndex()
                ->setEntryType(ProductsImagesType::class)

        ];
    }

}
