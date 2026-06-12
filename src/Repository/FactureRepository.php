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