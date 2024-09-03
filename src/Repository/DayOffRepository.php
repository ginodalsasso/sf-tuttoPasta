<?php

namespace App\Repository;

use App\Entity\DayOff;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DayOff>
 */
class DayOffRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DayOff::class); // Appel du constructeur de la classe parent
    }
    
    // Méthode permettant de récupérer tous les jours de congés
    public function findAllDayoffs(): array
    {
        $results = $this->createQueryBuilder('d')
            ->select('d.dayOff')
            ->getQuery()
            ->getResult();

        // Doctrine retourne des tableaux associatifs quand on utilise select()
        return array_map(function ($result) {
            // retourne la valeur de la clé 'dayOff'
            return $result['dayOff'];
        }, $results);
    }

    //    /**
    //     * @return DayOff[] Returns an array of DayOff objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('d.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?DayOff
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
