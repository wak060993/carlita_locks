<?php

namespace App\Controller;

use App\Repository\ClientRepository;
use App\Repository\ProduitRepository;
use App\Repository\RendezVousRepository;
use App\Repository\TransactionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    public function index(
        RendezVousRepository $rdvRepo,
        ClientRepository $clientRepo,
        TransactionRepository $transactionRepo,
        ProduitRepository $produitRepo
    ): Response {
        $aujourd_hui = new \DateTime();
        $debutMois = new \DateTime('first day of this month 00:00:00');
        $finMois = new \DateTime('last day of this month 23:59:59');

        // RDV aujourd'hui
        $rdvAujourdhui = $rdvRepo->findByDate($aujourd_hui, 1);
        $rdvCount = count($rdvAujourdhui);

        // Prochains RDV
        $prochainRdv = array_filter($rdvAujourdhui, function($rdv) {
            return !in_array($rdv->getStatut(), ['termine', 'annule', 'encaisse', 'no_show']);
        });

        // Total clients
        $totalClients = count($clientRepo->findBy(['salon' => 1]));

        // Nouveaux clients ce mois
        $nouveauxClients = $clientRepo->countNouveauxClients(1, $debutMois, $finMois);

        // Rapport journalier
        $rapport = $transactionRepo->getRapportJournalier(1);

        // Alertes stock
        $alertes = $produitRepo->findAlertes(1);

        // Recettes 7 derniers jours
        $recettes7Jours = $transactionRepo->getRecettes7Jours(1);

        // Top prestations du mois
        $topPrestations = $rdvRepo->getTopPrestations(1);

        // CA par mode de paiement
        $caParModePaiement = $transactionRepo->getCaParModePaiement(1, $debutMois, $finMois);

        // Taux no-show et annulation
        $statsRdv = $rdvRepo->getStatsRdv(1, $debutMois, $finMois);

        // Heures de pointe
        $heuresPointe = $rdvRepo->getHeuresPointe(1, $debutMois, $finMois);

        return $this->render('dashboard/index.html.twig', [
            'rdv_count' => $rdvCount,
            'prochain_rdv' => array_slice(array_values($prochainRdv), 0, 5),
            'total_clients' => $totalClients,
            'nouveaux_clients' => $nouveauxClients,
            'rapport' => $rapport,
            'alertes_stock' => count($alertes),
            'recettes_7_jours' => $recettes7Jours,
            'top_prestations' => $topPrestations,
            'ca_par_mode_paiement' => $caParModePaiement,
            'stats_rdv' => $statsRdv,
            'heures_pointe' => $heuresPointe,
        ]);
    }
}