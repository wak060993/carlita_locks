<?php

namespace App\Controller;

use App\Repository\RendezVousRepository;
use App\Repository\TransactionRepository;
use App\Repository\ClientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/rapports')]
class RapportController extends AbstractController
{
    #[Route('/', name: 'app_rapports')]
    public function index(): Response
    {
        return $this->redirectToRoute('app_rapport_mensuel');
    }

    #[Route('/hebdomadaire', name: 'app_rapport_hebdomadaire')]
    public function hebdomadaire(
        Request $request,
        TransactionRepository $transactionRepo,
        RendezVousRepository $rdvRepo
    ): Response {
        $dateParam = $request->query->get('date', date('Y-m-d'));
        $dateRef = new \DateTime($dateParam);

        $debutSemaine = clone $dateRef;
        $debutSemaine->modify('monday this week 00:00:00');
        $finSemaine = clone $debutSemaine;
        $finSemaine->modify('+6 days 23:59:59');

        // CA par jour
        $caParJour = [];
        $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
        $jour = clone $debutSemaine;
        for ($i = 0; $i < 7; $i++) {
            $debut = clone $jour;
            $debut->setTime(0, 0, 0);
            $fin = clone $jour;
            $fin->setTime(23, 59, 59);

            $total = $transactionRepo->getTotalPeriode(1, $debut, $fin);
            $caParJour[] = [
                'jour' => $jours[$i],
                'date' => $jour->format('d/m'),
                'total' => $total,
            ];
            $jour->modify('+1 day');
        }

        // Total semaine
        $totalSemaine = array_sum(array_column($caParJour, 'total'));

        // CA par employée
        $caParEmploye = $rdvRepo->getCaParEmploye(1, $debutSemaine, $finSemaine);

        // Top prestations
        $topPrestations = $rdvRepo->getTopPrestationsPeriode(1, $debutSemaine, $finSemaine);

        // Nb RDV
        $nbRdv = $rdvRepo->countRdvPeriode(1, $debutSemaine, $finSemaine);

        return $this->render('rapport/hebdomadaire.html.twig', [
            'ca_par_jour' => $caParJour,
            'total_semaine' => $totalSemaine,
            'ca_par_employe' => $caParEmploye,
            'top_prestations' => $topPrestations,
            'nb_rdv' => $nbRdv,
            'debut_semaine' => $debutSemaine,
            'fin_semaine' => $finSemaine,
            'semaine_precedente' => (clone $debutSemaine)->modify('-7 days')->format('Y-m-d'),
            'semaine_suivante' => (clone $debutSemaine)->modify('+7 days')->format('Y-m-d'),
        ]);
    }

    #[Route('/mensuel', name: 'app_rapport_mensuel')]
    public function mensuel(
        Request $request,
        TransactionRepository $transactionRepo,
        RendezVousRepository $rdvRepo,
        ClientRepository $clientRepo
    ): Response {
        $mois = $request->query->get('mois', date('Y-m'));
        $debutMois = new \DateTime($mois . '-01 00:00:00');
        $finMois = clone $debutMois;
        $finMois->modify('last day of this month 23:59:59');

        // CA par semaine du mois
        $caParSemaine = [];
        $semaine = 1;
        $debut = clone $debutMois;
        while ($debut <= $finMois) {
            $fin = clone $debut;
            $fin->modify('+6 days 23:59:59');
            if ($fin > $finMois) $fin = clone $finMois;

            $total = $transactionRepo->getTotalPeriode(1, $debut, $fin);
            $caParSemaine[] = [
                'label' => 'Sem. ' . $semaine,
                'debut' => $debut->format('d/m'),
                'fin' => $fin->format('d/m'),
                'total' => $total,
            ];
            $debut->modify('+7 days');
            $semaine++;
        }

        // Total mois
        $totalMois = $transactionRepo->getTotalPeriode(1, $debutMois, $finMois);

        // Mois précédent pour comparaison
        $debutMoisPrecedent = clone $debutMois;
        $debutMoisPrecedent->modify('-1 month');
        $finMoisPrecedent = clone $debutMoisPrecedent;
        $finMoisPrecedent->modify('last day of this month 23:59:59');
        $totalMoisPrecedent = $transactionRepo->getTotalPeriode(1, $debutMoisPrecedent, $finMoisPrecedent);

        // Evolution
        $evolution = $totalMoisPrecedent > 0
            ? round(($totalMois - $totalMoisPrecedent) / $totalMoisPrecedent * 100, 1)
            : 0;

        // CA par employée
        $caParEmploye = $rdvRepo->getCaParEmploye(1, $debutMois, $finMois);

        // Top prestations
        $topPrestations = $rdvRepo->getTopPrestationsPeriode(1, $debutMois, $finMois);

        // Top clients
        $topClients = $rdvRepo->getTopClients(1, $debutMois, $finMois);

        // Nb RDV
        $nbRdv = $rdvRepo->countRdvPeriode(1, $debutMois, $finMois);

        return $this->render('rapport/mensuel.html.twig', [
            'ca_par_semaine' => $caParSemaine,
            'total_mois' => $totalMois,
            'total_mois_precedent' => $totalMoisPrecedent,
            'evolution' => $evolution,
            'ca_par_employe' => $caParEmploye,
            'top_prestations' => $topPrestations,
            'top_clients' => $topClients,
            'nb_rdv' => $nbRdv,
            'debut_mois' => $debutMois,
            'fin_mois' => $finMois,
            'mois' => $mois,
            'mois_precedent' => $debutMoisPrecedent->format('Y-m'),
            'mois_suivant' => (clone $debutMois)->modify('+1 month')->format('Y-m'),
        ]);
    }
}