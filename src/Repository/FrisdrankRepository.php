<?php

namespace App\Repository;

use App\Entity\Frisdrank;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Frisdrank>
 *
 * @method Frisdrank|null find($id, $lockMode = null, $lockVersion = null)
 * @method Frisdrank|null findOneBy(array $criteria, array $orderBy = null)
 * @method Frisdrank[]    findAll()
 * @method Frisdrank[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FrisdrankRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Frisdrank::class);
    }

//    /**
//     * @return Frisdrank[] Returns an array of Frisdrank objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Frisdrank
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
