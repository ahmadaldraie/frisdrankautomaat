<?php

namespace App\Repository;

use App\Entity\Muntje;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Muntje>
 *
 * @method Muntje|null find($id, $lockMode = null, $lockVersion = null)
 * @method Muntje|null findOneBy(array $criteria, array $orderBy = null)
 * @method Muntje[]    findAll()
 * @method Muntje[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MuntjeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Muntje::class);
    }

//    /**
//     * @return Muntje[] Returns an array of Muntje objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Muntje
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
