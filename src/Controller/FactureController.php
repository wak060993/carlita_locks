<?php

namespace App\Controller;

use App\Repository\FactureRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/factures')]
class FactureController extends AbstractController
{
    #[Route('/', name: 'app_factures')]
    public function index(Request $request, FactureRepository $factureRepository): Response
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $search = $request->query->get('search', '');
        $statut = $request->query->get('statut', '');
        $dateDebut = $request->query->get('date_debut', '');
        $dateFin = $request->query->get('date_fin', '');

        $factures = $factureRepository->findWithFilters(
            1, $search, $statut, $dateDebut, $dateFin, $limit, $offset
        );

        $total = $factureRepository->countWithFilters(1, $search, $statut, $dateDebut, $dateFin);
        $totalPages = ceil($total / $limit);

        // Calcul des totaux
        $totalEncaisse = $factureRepository->getTotalEncaisse(1, $dateDebut, $dateFin);

        return $this->render('facture/index.html.twig', [
            'factures' => $factures,
            'search' => $search,
            'statut' => $statut,
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
            'page' => $page,
            'total_pages' => $totalPages,
            'total' => $total,
            'total_encaisse' => $totalEncaisse,
        ]);
    }
}