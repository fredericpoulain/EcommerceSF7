<?php

namespace App\Controller\Admin;

use App\Entity\Categories;
use App\Repository\CategoriesRepository;
use App\Service\ImageService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\RequestStack;
use Vich\UploaderBundle\Form\Type\VichImageType;

class CategoriesCrudController extends AbstractCrudController
{
//    private CategoriesRepository $categoriesRepository;

    public function __construct(
        private readonly CategoriesRepository $categoriesRepository,
        private readonly RequestStack $requestStack

    )
    {
    }
    public static function getEntityFqcn(): string
    {
        return Categories::class;
    }


    public function configureFields(string $pageName): iterable
    {
        $request = $this->requestStack->getCurrentRequest();
        $categoryId = $request?->query->get('entityId');
        $currentCategory = $categoryId ? $this->categoriesRepository->find($categoryId) : null;

        $hierarchicalCategories = $this->getHierarchicalCategories($currentCategory);

        return [
            IdField::new('id')
                ->hideOnForm(),
            TextField::new('name')
                ->setLabel('nom'),
            TextEditorField::new('description')
                ->setLabel('Description')
                ->hideOnIndex(),
//            TextField::new('slug')
//                ->hideOnIndex(),

            ChoiceField::new('catParent')
                ->setLabel('Catégorie parente')
                ->setChoices($hierarchicalCategories)
                ->hideOnIndex()->hideOnDetail(),

            textField::new('imageFile')->setFormType(VichImageType::class)->hideOnIndex(),
            ImageField::new('file')->setLabel('Image')->setBasePath('/images/categories')->onlyOnIndex(),
            SlugField::new('slug')->setTargetFieldName('name')->hideOnIndex()


        ];
    }

    private function getHierarchicalCategories($currentCategory): array
    {
        $idCurrentCategory = $currentCategory?->getId();
        $categories = $this->categoriesRepository->findTopLevelCategories();

        $hierarchicalCategories = [];
        foreach ($categories as $category) {
            // Exclusion de la catégorie actuelle de la liste des catégories parentes
            if ($idCurrentCategory !== $category->getId()) {
                if ($category->getCatParent() === null) {
                    $hierarchicalCategories[$category->getName()] = $category;
                    foreach ($category->getCategories() as $subCategory) {
                        // Exclure également la catégorie actuelle des sous-catégories
                        if ($idCurrentCategory !== $subCategory->getId()) {
                            $hierarchicalCategories['-------->' . $subCategory->getName()] = $subCategory;
                        }
                    }
                }
            }
        }
        return $hierarchicalCategories;
    }

}
