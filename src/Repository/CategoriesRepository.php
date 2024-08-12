<?php

namespace App\Repository;

use App\Entity\Categories;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Categories>
 *
 * @method Categories|null find($id, $lockMode = null, $lockVersion = null)
 * @method Categories|null findOneBy(array $criteria, array $orderBy = null)
 * @method Categories[]    findAll()
 * @method Categories[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoriesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Categories::class);
    }
    public function findAllStructured(): array
    {
        $categories = $this->findAll();
        $structured = [];

        foreach ($categories as $category) {
            if ($category->getCatParent() === null) {
                $structured[$category->getId()] = ['category' => $category, 'children' => []];
            } else {
                $structured[$category->getCatParent()->getId()]['children'][] = $category;
            }
        }

        return $structured;
    }
    public function findTopLevelCategories()
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.catParent', 'parent')
            ->addSelect('parent')
            ->where('c.catParent IS NULL OR parent.catParent IS NULL')
            ->getQuery()
            ->getResult();
    }
    public function findLowLevelCategories()
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('App\Entity\Categories', 'child', 'WITH', 'child.catParent = c.id')
            ->where('child.id IS NULL')
            ->getQuery()
            ->getResult();
    }
//    /**
//     * @return Categories[] Returns an array of Categories objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Categories
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
