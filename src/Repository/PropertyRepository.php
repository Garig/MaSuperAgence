<?php

namespace App\Repository;

use App\Entity\Property;
use App\Entity\PropertySearch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\Query;

/**
 * @method Property|null find($id, $lockMode = null, $lockVersion = null)
 * @method Property|null findOneBy(array $criteria, array $orderBy = null)
 * @method Property[]    findAll()
 * @method Property[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PropertyRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Property::class);
    }

    //la requete avec la pagination et la search
    /**
     * @return Query
     */
    public function findAllVisibleQuery(PropertySearch $search) : Query
    {
            $query = $this->createQueryBuilder('p');
            $query->select('p', 'pics');
            $query->leftJoin('p.pictures', 'pics');
            $query->andWhere('p.sold = false');
    
            if ($search->getMaxPrice()){
                $query = $query
                    ->andWhere('p.price <= :maxprice')
                    ->setParameter('maxprice', $search->getMaxPrice())
                    ->andWhere('p.sold = false');
            };

            if ($search->getMinSurface()){
                $query = $query
                    ->andWhere('p.surface >= :minsurface')
                    ->setParameter('minsurface', $search->getMinSurface())
                    ->andWhere('p.sold = false');
            }

            if ($search->getMinPiece()){
                $query = $query
                    ->andWhere('p.rooms >= :minpiece')
                    ->setParameter('minpiece', $search->getMinPiece())
                    ->andWhere('p.sold = false');
            }

            if ($search->getLat() && $search->getLng() && $search->getDistance()) {
                $query = $query
                    ->select('p')
                    ->andWhere('(6353 * 2 * ASIN(SQRT( POWER(SIN((p.lat - :lat) *  pi()/180 / 2), 2) +COS(p.lat * pi()/180) * COS(:lat * pi()/180) * POWER(SIN((p.lng - :lng) * pi()/180 / 2), 2) ))) <= :distance')
                    ->setParameter('lng', $search->getLng())
                    ->setParameter('lat', $search->getLat())
                    ->setParameter('distance', $search->getDistance());
            }

            if ($search->getOptions()->count() > 0){
                $k=0;
                foreach($search->getOptions() as $k => $option){
                    $k++;
                    $query = $query
                        ->andWhere(":option$k MEMBER OF p.options")
                        ->setParameter("option$k", $option);
                }
            }

            return $query->getQuery();
    }

    //préparation de la modification pour au dessus
    // public function findAllVisibleQuery() : Query
    // {
    //         $query = $this->createQueryBuilder('p') ;
    //         return $query->getQuery();
    // }


    //la requete simplement pour la pagination
    // /**
    //  * @return Query
    //  */
    // public function findAllVisibleQuery() : Query
    // {
    //         return $this->createQueryBuilder('p')
    //             ->andWhere('p.sold = false')
    //             ->getQuery();
    // }

    // Je la laisse mais on s'en sert plus
    // /**
    //  * @return Property[]
    //  */
    // public function findAllVisible() : array
    // {
    //         return $this->createQueryBuilder('p')
    //             ->andWhere('p.sold = false')
    //             ->getQuery()
    //             ->getResult();
    // }

    // /**
    //  * @return Property[]
    //  */
    // public function findLatest() : array
    // {
    //         return $this->createQueryBuilder('p')
    //             ->andWhere('p.sold = false')
    //             ->orderBy('p.id', 'DESC')
    //             ->setMaxResults(4)
    //             ->getQuery()
    //             ->getResult();
    // }

    // Pour faire un random des biens
    //  /**
    //  * @return Property[]
    //  */
    // public function findLatest() : array
    // {
    //         $count = $this->createQueryBuilder('p')
    //         ->select('COUNT(p)')
    //         ->getQuery()
    //         ->getSingleScalarResult();

    //         return $this->createQueryBuilder('p')
    //             ->andWhere('p.sold = false')
    //             // ->orderBy('p.id', 'DESC')
    //             ->setMaxResults(4)
    //             ->setFirstResult(rand(0, $count - 1))
    //             ->getQuery()
    //             ->getResult();
    // }
    
    // /**
    //  * @return Property[] Returns an array of Property objects
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
    public function findOneBySomeField($value): ?Property
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
