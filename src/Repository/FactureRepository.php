<?php

namespace App\Repository;

use App\Entity\Facture;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FactureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Facture::class);
    }

    public function findWithFilters(
        int $salonId,
        string $search = '',
        string $statut = '',
        string $dateDebut = '',
        string $dateFin = '',
        int $limit = 20,
        int $offset = 0
    ): array {
        $qb = $this->createQueryBuilder('f')
            ->join('f.client', 'c')
            ->join('f.transaction', 't')
            ->where('f.salon = :salon')
            ->setParameter('salon', $salonId)
            ->orderBy('f.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        if ($search) {
            $qb->andWhere('c.nom LIKE :search OR c.prenom LIKE :search OR f.numeroFacture LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        if ($statut) {
            $qb->andWhere('f.statut = :statut')
               ->setParameter('statut', $statut);
        }

        if ($dateDebut) {
            $qb->andWhere('f.createdAt >= :debut')
               ->setParameter('debut', new \DateTime($dateDebut . ' 00:00:00'));
        }

        if ($dateFin) {
            $qb->andWhere('f.createdAt <= :fin')
               ->setParameter('fin', new \DateTime($dateFin . ' 23:59:59'));
        }

        return $qb->getQuery()->getResult();
    }

    public function countWithFilters(
        int $salonId,
        string $search = '',
        string $statut = '',
        string $dateDebut = '',
        string $dateFin = ''
    ): int {
        $qb = $this->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->join('f.client', 'c')
            ->where('f.salon = :salon')
            ->setParameter('salon', $salonId);

        if ($search) {
            $qb->andWhere('c.nom LIKE :search OR c.prenom LIKE :search OR f.numeroFacture LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        if ($statut) {
            $qb->andWhere('f.statut = :statut')
               ->setParameter('statut', $statut);
        }

        if ($dateDebut) {
            $qb->andWhere('f.createdAt >= :debut')
               ->setParameter('debut', new \DateTime($dateDebut . ' 00:00:00'));
        }

        if ($dateFin) {
            $qb->andWhere('f.createdAt <= :fin')
               ->setParameter('fin', new \DateTime($dateFin . ' 23:59:59'));
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function getTotalEncaisse(int $salonId, string $dateDebut = '', string $dateFin = ''): float
    {
        $qb = $this->createQueryBuilder('f')
            ->select('SUM(f.montantTotal)')
            ->where('f.salon = :salon')
            ->andWhere('f.statut = :statut')
            ->setParameter('salon', $salonId)
            ->setParameter('statut', 'payee');

        if ($dateDebut) {
            $qb->andWhere('f.createdAt >= :debut')
               ->setParameter('debut', new \DateTime($dateDebut . ' 00:00:00'));
        }

        if ($dateFin) {
            $qb->andWhere('f.createdAt <= :fin')
               ->setParameter('fin', new \DateTime($dateFin . ' 23:59:59'));
        }

        return (float) $qb->getQuery()->getSingleScalarResult();
    }

    public function findByDate(\DateTimeInterface $date, int $salonId): array
    {
        $debut = new \DateTime($date->format('Y-m-d') . ' 00:00:00');
        $fin = new \DateTime($date->format('Y-m-d') . ' 23:59:59');

        return $this->createQueryBuilder('f')
            ->where('f.salon = :salon')
            ->andWhere('f.createdAt >= :debut')
            ->andWhere('f.createdAt <= :fin')
            ->setParameter('salon', $salonId)
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->orderBy('f.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}