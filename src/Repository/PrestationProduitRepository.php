<?php

namespace App\Repository;

use App\Entity\PrestationProduit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PrestationProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PrestationProduit::class);
    }

    public function findByPrestation(int $prestationId): array
    {
        return $this->createQueryBuilder('pp')
            ->where('pp.prestation = :prestation')
            ->setParameter('prestation', $prestationId)
            ->getQuery()
            ->getResult();
    }
}