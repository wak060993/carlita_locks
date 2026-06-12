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

   public function findRdvAEncaisser(int $salonId): array
{
    $debut = new \DateTime('today 00:00:00');
    $fin = new \DateTime('today 23:59:59');

    return $this->createQueryBuilder('r')
        ->where('r.salon = :salon')
        ->andWhere('(r.statut = :termine OR r.statut = :en_cours)')
        ->andWhere('r.dateHeureDebut >= :debut')
        ->andWhere('r.dateHeureDebut <= :fin')
        ->setParameter('salon', $salonId)
        ->setParameter('termine', 'termine')
        ->setParameter('en_cours', 'en_cours')
        ->setParameter('debut', $debut)
        ->setParameter('fin', $fin)
        ->orderBy('r.dateHeureDebut', 'ASC')
        ->getQuery()
        ->getResult();
}

    public function findConflits(
        int $employeId,
        \DateTimeInterface $dateDebut,
        \DateTimeInterface $dateFin,
        ?int $rdvIdExclu = null
    ): array {
        $qb = $this->createQueryBuilder('r')
            ->where('r.employe = :employe')
            ->andWhere('r.statut != :annule')
            ->andWhere('r.statut != :no_show')
            ->andWhere('(r.dateHeureDebut < :fin AND r.dateHeureFin > :debut)')
            ->setParameter('employe', $employeId)
            ->setParameter('annule', 'annule')
            ->setParameter('no_show', 'no_show')
            ->setParameter('debut', $dateDebut)
            ->setParameter('fin', $dateFin);

        if ($rdvIdExclu) {
            $qb->andWhere('r.id != :exclu')
               ->setParameter('exclu', $rdvIdExclu);
        }

        return $qb->getQuery()->getResult();
    }
}