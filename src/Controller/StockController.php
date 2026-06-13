<?php

namespace App\Controller;

use App\Entity\MouvementStock;
use App\Entity\Produit;
use App\Repository\MouvementStockRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/stock')]
class StockController extends AbstractController
{
    #[Route('/', name: 'app_stock')]
    public function index(ProduitRepository $produitRepository): Response
    {
        $produits = $produitRepository->findBy(['salon' => 1], ['nom' => 'ASC']);
        $alertes = $produitRepository->findAlertes(1);

        return $this->render('stock/index.html.twig', [
            'produits' => $produits,
            'alertes' => $alertes,
        ]);
    }

    #[Route('/nouveau', name: 'app_produit_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $produit = new Produit();
            $salon = $em->getRepository(\App\Entity\Salon::class)->find(1);
            $produit->setSalon($salon);
            $produit->setNom($request->request->get('nom'));
            $produit->setCategorie($request->request->get('categorie'));
            $produit->setQuantiteStock((int) $request->request->get('quantite_stock'));
            $produit->setSeuilAlerte((int) $request->request->get('seuil_alerte'));
            $produit->setPrixAchat($request->request->get('prix_achat') ?: null);
            $produit->setPrixVente($request->request->get('prix_vente') ?: null);
            $produit->setCreatedAt(new \DateTimeImmutable());
            $em->persist($produit);
            $em->flush();

            $this->addFlash('success', 'Produit ajouté avec succès !');
            return $this->redirectToRoute('app_stock');
        }

        return $this->render('stock/new.html.twig');
    }

    #[Route('/{id}/modifier', name: 'app_produit_edit')]
    public function edit(Produit $produit, Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $produit->setNom($request->request->get('nom'));
            $produit->setCategorie($request->request->get('categorie'));
            $produit->setSeuilAlerte((int) $request->request->get('seuil_alerte'));
            $produit->setPrixAchat($request->request->get('prix_achat') ?: null);
            $produit->setPrixVente($request->request->get('prix_vente') ?: null);
            $em->flush();

            $this->addFlash('success', 'Produit modifié avec succès !');
            return $this->redirectToRoute('app_stock');
        }

        return $this->render('stock/edit.html.twig', [
            'produit' => $produit,
        ]);
    }

    #[Route('/{id}/mouvement', name: 'app_stock_mouvement', methods: ['POST'])]
    public function mouvement(Produit $produit, Request $request, EntityManagerInterface $em): Response
    {
        $type = $request->request->get('type');
        $quantite = (int) $request->request->get('quantite');
        $motif = $request->request->get('motif');

        $mouvement = new MouvementStock();
        $mouvement->setProduit($produit);
        $mouvement->setEmploye($this->getUser());
        $mouvement->setType($type);
        $mouvement->setQuantite($quantite);
        $mouvement->setMotif($motif);
        $mouvement->setCreatedAt(new \DateTimeImmutable());
        $em->persist($mouvement);

        // Mettre à jour le stock
        if ($type === 'entree') {
            $produit->setQuantiteStock($produit->getQuantiteStock() + $quantite);
        } else {
            $produit->setQuantiteStock(max(0, $produit->getQuantiteStock() - $quantite));
        }

        $em->flush();

        $this->addFlash('success', 'Mouvement de stock enregistré !');
        return $this->redirectToRoute('app_stock');
    }

    #[Route('/{id}/supprimer', name: 'app_produit_delete', methods: ['POST'])]
    public function delete(Produit $produit, EntityManagerInterface $em): Response
    {
        $em->remove($produit);
        $em->flush();

        $this->addFlash('success', 'Produit supprimé avec succès !');
        return $this->redirectToRoute('app_stock');
    }

    #[Route('/{id}/historique', name: 'app_stock_historique')]
    public function historique(Produit $produit, MouvementStockRepository $mouvementRepo): Response
    {
        $mouvements = $mouvementRepo->findBy(
            ['produit' => $produit],
            ['createdAt' => 'DESC']
        );

        return $this->render('stock/historique.html.twig', [
            'produit' => $produit,
            'mouvements' => $mouvements,
        ]);
    }
}