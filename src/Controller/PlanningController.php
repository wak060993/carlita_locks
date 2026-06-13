<?php

namespace App\Controller;

use App\Repository\RendezVousRepository;
use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/planning')]
class PlanningController extends AbstractController
{
    #[Route('/', name: 'app_planning')]
    public function index(
        Request $request,
        RendezVousRepository $rdvRepo,
        UtilisateurRepository $utilisateurRepo
    ): Response {
        // Semaine courante ou celle demandée
        $dateParam = $request->query->get('date', date('Y-m-d'));
        $dateRef = new \DateTime($dateParam);

        // Début et fin de la semaine (lundi au samedi)
        $debutSemaine = clone $dateRef;
        $debutSemaine->modify('monday this week');
        $finSemaine = clone $debutSemaine;
        $finSemaine->modify('+5 days'); // Samedi

        // Générer les jours de la semaine
        $jours = [];
        $jour = clone $debutSemaine;
        for ($i = 0; $i < 6; $i++) {
            $jours[] = clone $jour;
            $jour->modify('+1 day');
        }

        // Récupérer tous les employés actifs
        $employes = $utilisateurRepo->findBy(['actif' => true]);

        // Récupérer tous les RDV de la semaine
        $rdvSemaine = $rdvRepo->findBySemaine($debutSemaine, $finSemaine, 1);

        // Organiser les RDV par jour et par employé
        $planning = [];
        foreach ($rdvSemaine as $rdv) {
            $jourKey = $rdv->getDateHeureDebut()->format('Y-m-d');
            $employeId = $rdv->getEmploye()->getId();
            $planning[$jourKey][$employeId][] = $rdv;
        }

        return $this->render('planning/index.html.twig', [
            'jours' => $jours,
            'employes' => $employes,
            'planning' => $planning,
            'debut_semaine' => $debutSemaine,
            'fin_semaine' => $finSemaine,
            'semaine_precedente' => (clone $debutSemaine)->modify('-7 days')->format('Y-m-d'),
            'semaine_suivante' => (clone $debutSemaine)->modify('+7 days')->format('Y-m-d'),
        ]);
    }
}