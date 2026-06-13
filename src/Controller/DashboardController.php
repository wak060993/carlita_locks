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

        // RDV aujourd'hui
        $rdvAujourdhui = $rdvRepo->findByDate($aujourd_hui, 1);
        $rdvCount = count($rdvAujourdhui);

        // Prochains RDV (non terminés, non annulés)
        $prochainRdv = array_filter($rdvAujourdhui, function($rdv) {
            return !in_array($rdv->getStatut(), ['termine', 'annule', 'encaisse', 'no_show']);
        });

        // Total clients
        $totalClients = count($clientRepo->findBy(['salon' => 1]));

        // Rapport journalier
        $rapport = $transactionRepo->getRapportJournalier(1);

        // Alertes stock
        $alertes = $produitRepo->findAlertes(1);

        // Recettes 7 derniers jours
        $recettes7Jours = $transactionRepo->getRecettes7Jours(1);

        // Top prestations du mois
        $topPrestations = $rdvRepo->getTopPrestations(1);

        return $this->render('dashboard/index.html.twig', [
            'rdv_count' => $rdvCount,
            'prochain_rdv' => array_slice(array_values($prochainRdv), 0, 5),
            'total_clients' => $totalClients,
            'rapport' => $rapport,
            'alertes_stock' => count($alertes),
            'recettes_7_jours' => $recettes7Jours,
            'top_prestations' => $topPrestations,
        ]);
    }
}