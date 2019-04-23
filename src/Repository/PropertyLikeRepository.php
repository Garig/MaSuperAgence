<?php

namespace App\Repository;

use App\Entity\PropertyLike;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PropertyLike|null find($id, $lockMode = null, $lockVersion = null)
 * @method PropertyLike|null findOneBy(array $criteria, array $orderBy = null)
 * @method PropertyLike[]    findAll()
 * @method PropertyLike[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PropertyLikeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PropertyLike::class);
    }

    // /**
    //  * @return PropertyLike[] Returns an array of PropertyLike objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PropertyLike
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
