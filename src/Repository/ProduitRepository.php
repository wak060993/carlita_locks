<?php

namespace App\Repository;

use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }

    public function findAlertes(int $salonId): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.salon = :salon')
            ->andWhere('p.quantiteStock <= p.seuilAlerte')
            ->setParameter('salon', $salonId)
            ->orderBy('p.quantiteStock', 'ASC')
            ->getQuery()
            ->getResult();
    }
}