<?php

namespace App\Repository;

use App\Entity\Transaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    public function getRapportJournalier(int $salonId): array
    {
        $debut = new \DateTime('today 00:00:00');
        $fin = new \DateTime('today 23:59:59');

        $transactions = $this->createQueryBuilder('t')
            ->where('t.salon = :salon')
            ->andWhere('t.createdAt >= :debut')
            ->andWhere('t.createdAt <= :fin')
            ->setParameter('salon', $salonId)
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        $totalEntrees = 0;
        $totalSorties = 0;
        $details = [];

        foreach ($transactions as $t) {
            if ($t->getType() === 'entree') {
                $totalEntrees += $t->getMontant();
            } else {
                $totalSorties += $t->getMontant();
            }

            $details[] = [
                'id' => $t->getId(),
                'type' => $t->getType(),
                'categorie' => $t->getCategorie(),
                'montant' => $t->getMontant(),
                'mode_paiement' => $t->getModePaiement(),
                'description' => $t->getDescription(),
                'heure' => $t->getCreatedAt()->format('H:i'),
                'employe' => $t->getEmploye()->getPrenom() . ' ' . $t->getEmploye()->getNom(),
            ];
        }

        return [
            'total_entrees' => $totalEntrees,
            'total_sorties' => $totalSorties,
            'solde' => $totalEntrees - $totalSorties,
            'nb_transactions' => count($transactions),
            'transactions' => $details,
        ];
    }

    public function getRecettes7Jours(int $salonId): array
{
    $result = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = new \DateTime("-{$i} days");
        $debut = new \DateTime("-{$i} days 00:00:00");
        $fin = new \DateTime("-{$i} days 23:59:59");

        $total = $this->createQueryBuilder('t')
            ->select('SUM(t.montant)')
            ->where('t.salon = :salon')
            ->andWhere('t.type = :type')
            ->andWhere('t.createdAt >= :debut')
            ->andWhere('t.createdAt <= :fin')
            ->setParameter('salon', $salonId)
            ->setParameter('type', 'entree')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->getQuery()
            ->getSingleScalarResult();

        $result[] = [
            'date' => $date->format('d/m'),
            'total' => (float) ($total ?? 0),
        ];
    }

    return $result;
}
}