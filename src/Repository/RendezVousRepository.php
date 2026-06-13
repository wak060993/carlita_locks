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

    public function getTopPrestations(int $salonId): array
{
    $debut = new \DateTime('first day of this month 00:00:00');
    $fin = new \DateTime('last day of this month 23:59:59');

    return $this->createQueryBuilder('r')
        ->select('p.nom, p.prix, COUNT(r.id) as nb')
        ->join('r.prestation', 'p')
        ->where('r.salon = :salon')
        ->andWhere('r.statut IN (:statuts)')
        ->andWhere('r.dateHeureDebut >= :debut')
        ->andWhere('r.dateHeureDebut <= :fin')
        ->setParameter('salon', $salonId)
        ->setParameter('statuts', ['termine', 'encaisse'])
        ->setParameter('debut', $debut)
        ->setParameter('fin', $fin)
        ->groupBy('p.id')
        ->orderBy('nb', 'DESC')
        ->setMaxResults(5)
        ->getQuery()
        ->getResult();
}

public function findBySemaine(\DateTimeInterface $debut, \DateTimeInterface $fin, int $salonId): array
{
    $finJour = clone $fin;
    $finJour->modify('23:59:59');

    return $this->createQueryBuilder('r')
        ->where('r.salon = :salon')
        ->andWhere('r.dateHeureDebut >= :debut')
        ->andWhere('r.dateHeureDebut <= :fin')
        ->andWhere('r.statut != :annule')
        ->andWhere('r.statut != :no_show')
        ->setParameter('salon', $salonId)
        ->setParameter('debut', $debut)
        ->setParameter('fin', $finJour)
        ->setParameter('annule', 'annule')
        ->setParameter('no_show', 'no_show')
        ->orderBy('r.dateHeureDebut', 'ASC')
        ->getQuery()
        ->getResult();
}
public function getCaParEmploye(int $salonId, \DateTimeInterface $debut, \DateTimeInterface $fin): array
{
    return $this->createQueryBuilder('r')
        ->select('u.prenom, u.nom, SUM(p.prix) as ca, COUNT(r.id) as nb')
        ->join('r.employe', 'u')
        ->join('r.prestation', 'p')
        ->where('r.salon = :salon')
        ->andWhere('r.statut IN (:statuts)')
        ->andWhere('r.dateHeureDebut >= :debut')
        ->andWhere('r.dateHeureDebut <= :fin')
        ->setParameter('salon', $salonId)
        ->setParameter('statuts', ['termine', 'encaisse'])
        ->setParameter('debut', $debut)
        ->setParameter('fin', $fin)
        ->groupBy('u.id')
        ->orderBy('ca', 'DESC')
        ->getQuery()
        ->getResult();
}

public function getTopPrestationsPeriode(int $salonId, \DateTimeInterface $debut, \DateTimeInterface $fin): array
{
    return $this->createQueryBuilder('r')
        ->select('p.nom, p.prix, COUNT(r.id) as nb, SUM(p.prix) as ca')
        ->join('r.prestation', 'p')
        ->where('r.salon = :salon')
        ->andWhere('r.statut IN (:statuts)')
        ->andWhere('r.dateHeureDebut >= :debut')
        ->andWhere('r.dateHeureDebut <= :fin')
        ->setParameter('salon', $salonId)
        ->setParameter('statuts', ['termine', 'encaisse'])
        ->setParameter('debut', $debut)
        ->setParameter('fin', $fin)
        ->groupBy('p.id')
        ->orderBy('nb', 'DESC')
        ->setMaxResults(5)
        ->getQuery()
        ->getResult();
}

public function getTopClients(int $salonId, \DateTimeInterface $debut, \DateTimeInterface $fin): array
{
    return $this->createQueryBuilder('r')
        ->select('c.prenom, c.nom, COUNT(r.id) as nb, SUM(p.prix) as ca')
        ->join('r.client', 'c')
        ->join('r.prestation', 'p')
        ->where('r.salon = :salon')
        ->andWhere('r.statut IN (:statuts)')
        ->andWhere('r.dateHeureDebut >= :debut')
        ->andWhere('r.dateHeureDebut <= :fin')
        ->setParameter('salon', $salonId)
        ->setParameter('statuts', ['termine', 'encaisse'])
        ->setParameter('debut', $debut)
        ->setParameter('fin', $fin)
        ->groupBy('c.id')
        ->orderBy('ca', 'DESC')
        ->setMaxResults(5)
        ->getQuery()
        ->getResult();
}

public function countRdvPeriode(int $salonId, \DateTimeInterface $debut, \DateTimeInterface $fin): int
{
    return (int) $this->createQueryBuilder('r')
        ->select('COUNT(r.id)')
        ->where('r.salon = :salon')
        ->andWhere('r.statut IN (:statuts)')
        ->andWhere('r.dateHeureDebut >= :debut')
        ->andWhere('r.dateHeureDebut <= :fin')
        ->setParameter('salon', $salonId)
        ->setParameter('statuts', ['termine', 'encaisse'])
        ->setParameter('debut', $debut)
        ->setParameter('fin', $fin)
        ->getQuery()
        ->getSingleScalarResult();
}

public function getStatsRdv(int $salonId, \DateTimeInterface $debut, \DateTimeInterface $fin): array
{
    $total = (int) $this->createQueryBuilder('r')
        ->select('COUNT(r.id)')
        ->where('r.salon = :salon')
        ->andWhere('r.dateHeureDebut >= :debut')
        ->andWhere('r.dateHeureDebut <= :fin')
        ->setParameter('salon', $salonId)
        ->setParameter('debut', $debut)
        ->setParameter('fin', $fin)
        ->getQuery()
        ->getSingleScalarResult();

    $noShow = (int) $this->createQueryBuilder('r')
        ->select('COUNT(r.id)')
        ->where('r.salon = :salon')
        ->andWhere('r.statut = :statut')
        ->andWhere('r.dateHeureDebut >= :debut')
        ->andWhere('r.dateHeureDebut <= :fin')
        ->setParameter('salon', $salonId)
        ->setParameter('statut', 'no_show')
        ->setParameter('debut', $debut)
        ->setParameter('fin', $fin)
        ->getQuery()
        ->getSingleScalarResult();

    $annules = (int) $this->createQueryBuilder('r')
        ->select('COUNT(r.id)')
        ->where('r.salon = :salon')
        ->andWhere('r.statut = :statut')
        ->andWhere('r.dateHeureDebut >= :debut')
        ->andWhere('r.dateHeureDebut <= :fin')
        ->setParameter('salon', $salonId)
        ->setParameter('statut', 'annule')
        ->setParameter('debut', $debut)
        ->setParameter('fin', $fin)
        ->getQuery()
        ->getSingleScalarResult();

    return [
        'total' => $total,
        'no_show' => $noShow,
        'annules' => $annules,
        'honores' => $total - $noShow - $annules,
        'taux_no_show' => $total > 0 ? round($noShow / $total * 100, 1) : 0,
        'taux_annulation' => $total > 0 ? round($annules / $total * 100, 1) : 0,
    ];
}

public function getHeuresPointe(int $salonId, \DateTimeInterface $debut, \DateTimeInterface $fin): array
{
    $results = $this->createQueryBuilder('r')
        ->select('SUBSTRING(r.dateHeureDebut, 12, 2) as heure, COUNT(r.id) as nb')
        ->where('r.salon = :salon')
        ->andWhere('r.dateHeureDebut >= :debut')
        ->andWhere('r.dateHeureDebut <= :fin')
        ->andWhere('r.statut != :annule')
        ->setParameter('salon', $salonId)
        ->setParameter('debut', $debut)
        ->setParameter('fin', $fin)
        ->setParameter('annule', 'annule')
        ->groupBy('heure')
        ->orderBy('nb', 'DESC')
        ->setMaxResults(5)
        ->getQuery()
        ->getResult();

    return $results;
}
}