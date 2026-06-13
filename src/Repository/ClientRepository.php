<?php

namespace App\Repository;

use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }
    public function search(string $query, int $salonId): array
{
    return $this->createQueryBuilder('c')
        ->where('c.salon = :salon')
        ->andWhere(
            'c.nom LIKE :q OR c.prenom LIKE :q OR c.telephone LIKE :q OR c.whatsapp LIKE :q'
        )
        ->setParameter('salon', $salonId)
        ->setParameter('q', '%' . $query . '%')
        ->orderBy('c.nom', 'ASC')
        ->getQuery()
        ->getResult();
}
public function countNouveauxClients(int $salonId, \DateTimeInterface $debut, \DateTimeInterface $fin): int
{
    return (int) $this->createQueryBuilder('c')
        ->select('COUNT(c.id)')
        ->where('c.salon = :salon')
        ->andWhere('c.createdAt >= :debut')
        ->andWhere('c.createdAt <= :fin')
        ->setParameter('salon', $salonId)
        ->setParameter('debut', $debut)
        ->setParameter('fin', $fin)
        ->getQuery()
        ->getSingleScalarResult();
}
}

