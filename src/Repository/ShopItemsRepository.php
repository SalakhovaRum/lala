<?php

namespace App\Repository;

use App\Entity\ShopItems;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ShopItems>
 *
 * @method ShopItems|null find($id, $lockMode = null, $lockVersion = null)
 * @method ShopItems|null findOneBy(array $criteria, array $orderBy = null)
 * @method ShopItems[]    findAll()
 * @method ShopItems[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShopItemsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShopItems::class);
    }

//    /**
//     * @return ShopItems[] Returns an array of ShopItems objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ShopItems
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
// В ShopItemsRepository.php

    public function findBySearchQuery(string $query): array
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.title LIKE :query OR i.defcription LIKE :query')
            ->setParameter('query', '%'.$query.'%')
            ->getQuery()
            ->getResult();
    }

}
