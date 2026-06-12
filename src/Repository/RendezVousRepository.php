<?php

namespace App\Repository;

use App\Entity\RendezVous;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RendezVousRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RendezVous::class);
    }

    public function findByDate(\DateTimeInterface $date, int $salonId): array
    {
        $debut = new \DateTime($date->format('Y-m-d') . ' 00:00:00');
        $fin = new \DateTime($date->format('Y-m-d') . ' 23:59:59');

        return $this->createQueryBuilder('r')
            ->where('r.dateHeureDebut >= :debut')
            ->andWhere('r.dateHeureDebut <= :fin')
            ->andWhere('r.salon = :salon')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->setParameter('salon', $salonId)
            ->orderBy('r.dateHeureDebut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByEmployeAndDate(int $employeId, \DateTimeInterface $date): array
    {
        $debut = new \DateTime($date->format('Y-m-d') . ' 00:00:00');
        $fin = new \DateTime($date->format('Y-m-d') . ' 23:59:59');

        return $this->createQueryBuilder('r')
            ->where('r.employe = :employe')
            ->andWhere('r.dateHeureDebut >= :debut')
            ->andWhere('r.dateHeureDebut <= :fin')
            ->andWhere('r.statut != :annule')
            ->setParameter('employe', $employeId)
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->setParameter('annule', 'annule')
            ->orderBy('r.dateHeureDebut', 'ASC')
            ->getQuery()
            ->getResult();
    }
}