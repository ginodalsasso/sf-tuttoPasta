<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Quote;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Quote>
 */
class QuoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Quote::class);
    }

    //    /**
    //     * @return Quote[] Returns an array of Quote objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('q')
    //            ->andWhere('q.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('q.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    public function findOneByNameOrEmail(string $name): array
    {
        return $this->createQueryBuilder('q')
            ->where('q.customerName LIKE :name OR q.customerEmail LIKE :name')
            ->setParameter('name', '%' . $name . '%')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Quote[] Returns an array of Quotes objects
    //     */
    // Requête pour récupérer les devis par utilisateur
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('q')
            ->innerJoin('q.appointments', 'a')
            ->andWhere('a.user = :user')
            ->setParameter('user', $user)
            ->orderBy('q.quoteDate', 'DESC') // Trié par date de début
            ->getQuery()
            ->getResult()
        ;
    }

    // SELECT q.*
    // FROM appointment a
    // INNER JOIN quote q ON a.id = q.appointments_id
    // WHERE a.user_id = :user
    // ORDER BY q.quote_date DESC;


    // Requête pour récupérer le user par le devis
    public function findUserByQuote(Quote $quote): ?User
    {
        return $this->createQueryBuilder('q')
            ->innerJoin('q.appointments', 'a')
            ->andWhere('q.id = :quote')
            ->setParameter('quote', $quote->getId())
            ->select('user') 
            ->addSelect('a.user')
            ->getQuery()
            ->getOneOrNullResult();
    }


    // Requête pour récupérer les devis par statut
    public function countQuotesByState(): array
    {
        $qb = $this->createQueryBuilder('q')
            ->select('q.state, COUNT(q.id) as count')
            ->groupBy('q.state');

        return $qb->getQuery()->getResult();
    }

    // Requête pour récupérer le total TTC par mois
    public function getTotalTTCByMonth(): array
    {
        $qb = $this->createQueryBuilder('q')
            ->select('YEAR(q.quoteDate) as year, MONTH(q.quoteDate) as month, SUM(q.totalTTC) as totalTTC')
            ->groupBy('year, month')
            ->orderBy('year', 'ASC')
            ->addOrderBy('month', 'ASC');

        return $qb->getQuery()->getResult();
    }

    //    public function findOneBySomeField($value): ?Quote
    //    {
    //        return $this->createQueryBuilder('q')
    //            ->andWhere('q.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
