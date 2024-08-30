<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Appointment;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Appointment>
 */
class AppointmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Appointment::class);
    }


    public function findAllRDV($startDate)
    {
        // Initialisation des temps de début et de fin de la journée de travail
        $startTime = clone $startDate;
        $startTime->setTime(9, 0, 0);

        $endTime = clone $startTime;
        $endTime->setTime(17, 0, 0);

        // Création d'une requête pour trouver tous les rendez-vous existants pour cette journée
        $booking = $this->createQueryBuilder('a')
            ->select('a.startDate')
            ->andWhere('a.startDate >= :start')
            ->andWhere('a.endDate <= :end')
            ->setParameter('start', $startTime)
            ->setParameter('end', $endTime)
            ->getQuery()
            ->getResult();

        // Génération de tous les créneaux horaires possibles pour la journée
        $bookedSlots = [];
        // Parcours des rendez-vous pour récupérer les créneaux déjà réservés
        foreach($booking as $booked) {
            $bookedSlots[] = $booked["startDate"]->format('Y-m-d H:i:s');
        }
        // Génération des créneaux disponibles
        $interval = new \DateInterval('PT1H'); // Intervalle d'une heure
        $slots = [];

        // Parcours de la journée par tranches d'une heure
        for ($time = clone $startTime; $time < $endTime; $time->add($interval)) {
            $slot = $time->format('Y-m-d H:i:s');

            // Si le créneau n'est pas déjà réservé, on l'ajoute aux créneaux disponibles 
            if (!in_array($slot, $bookedSlots)) { 
                $slots[] = $slot;
            }
        }
        // Retourne un tableau contenant les créneaux disponibles et les créneaux réservés
        return [$slots, $bookedSlots];
    }

    //    /**
    //     * @return Appointment[] Returns an array of Appointment objects
    //     */
    // Requête pour récupérer les RDV user
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.user = :user')
            ->setParameter('user', $user)
            ->orderBy('a.startDate', 'DESC') //Trié par date de début
            ->getQuery()
            ->getResult()
        ;
    }

    // SELECT *
    // FROM appointment a
    // WHERE a.user_id = :user_id
    // ORDER BY a.startDate DESC;

    // Requête pour récupérer les derniers RDV 
    public function findLatestAppointments(int $limit = 3)
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.startDate', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    //    public function findOneBySomeField($value): ?Appointment
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}