<?php

namespace App\Controller;

use App\Entity\Prestation;
use App\Entity\PrestationProduit;
use App\Repository\PrestationRepository;
use App\Repository\ProduitRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/prestations')]
class PrestationController extends AbstractController
{
    #[Route('/', name: 'app_prestations')]
    public function index(PrestationRepository $prestationRepository): Response
    {
        $prestations = $prestationRepository->findAll();
        return $this->render('prestation/index.html.twig', [
            'prestations' => $prestations,
        ]);
    }

    #[Route('/nouveau', name: 'app_prestation_new')]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        UtilisateurRepository $utilisateurRepository,
        ProduitRepository $produitRepository
    ): Response {
        $employes = $utilisateurRepository->findBy(['actif' => true]);
        $produits = $produitRepository->findBy(['salon' => 1]);

        if ($request->isMethod('POST')) {
            $prestation = new Prestation();
            $salon = $em->getRepository(\App\Entity\Salon::class)->find(1);
            $prestation->setSalon($salon);
            $prestation->setNom($request->request->get('nom'));
            $prestation->setCategorie($request->request->get('categorie'));
            $prestation->setDescription($request->request->get('description'));
            $prestation->setDureeMinutes((int) $request->request->get('duree_minutes'));
            $prestation->setPrix($request->request->get('prix'));
            $prestation->setActif(true);
            $prestation->setCreatedAt(new \DateTimeImmutable());

            // Commission optionnelle
            $commission = $request->request->get('commission_pourcentage');
            if ($commission !== null && $commission !== '') {
                $prestation->setCommissionPourcentage($commission);
            }

            // Employés autorisés
            $employeIds = $request->request->all('employes') ?? [];
            foreach ($employeIds as $employeId) {
                $employe = $utilisateurRepository->find($employeId);
                if ($employe) {
                    $prestation->addEmploye($employe);
                }
            }

            // Produits utilisés
            $produitIds = $request->request->all('produits') ?? [];
            $quantites = $request->request->all('quantites') ?? [];
            foreach ($produitIds as $index => $produitId) {
                $produit = $produitRepository->find($produitId);
                if ($produit) {
                    $pp = new PrestationProduit();
                    $pp->setProduit($produit);
                    $pp->setQuantiteUtilisee((int) ($quantites[$index] ?? 1));
                    $prestation->addPrestationProduit($pp);
                    $em->persist($pp);
                }
            }

            $em->persist($prestation);
            $em->flush();

            $this->addFlash('success', 'Prestation ajoutée avec succès !');
            return $this->redirectToRoute('app_prestations');
        }

        return $this->render('prestation/new.html.twig', [
            'employes' => $employes,
            'produits' => $produits,
        ]);
    }

    #[Route('/{id}/modifier', name: 'app_prestation_edit')]
    public function edit(
        Prestation $prestation,
        Request $request,
        EntityManagerInterface $em,
        UtilisateurRepository $utilisateurRepository,
        ProduitRepository $produitRepository
    ): Response {
        $employes = $utilisateurRepository->findBy(['actif' => true]);
        $produits = $produitRepository->findBy(['salon' => 1]);

        if ($request->isMethod('POST')) {
            $prestation->setNom($request->request->get('nom'));
            $prestation->setCategorie($request->request->get('categorie'));
            $prestation->setDescription($request->request->get('description'));
            $prestation->setDureeMinutes((int) $request->request->get('duree_minutes'));
            $prestation->setPrix($request->request->get('prix'));
            $prestation->setActif($request->request->get('actif') ? true : false);

            // Commission
            $commission = $request->request->get('commission_pourcentage');
            $prestation->setCommissionPourcentage($commission !== '' ? $commission : null);

            // Mise à jour employés
            foreach ($prestation->getEmployes() as $employe) {
                $prestation->removeEmploye($employe);
            }
            $employeIds = $request->request->all('employes') ?? [];
            foreach ($employeIds as $employeId) {
                $employe = $utilisateurRepository->find($employeId);
                if ($employe) {
                    $prestation->addEmploye($employe);
                }
            }

            // Mise à jour produits
            foreach ($prestation->getPrestationProduits() as $pp) {
                $prestation->removePrestationProduit($pp);
                $em->remove($pp);
            }
            $produitIds = $request->request->all('produits') ?? [];
            $quantites = $request->request->all('quantites') ?? [];
            foreach ($produitIds as $index => $produitId) {
                $produit = $produitRepository->find($produitId);
                if ($produit) {
                    $pp = new PrestationProduit();
                    $pp->setProduit($produit);
                    $pp->setQuantiteUtilisee((int) ($quantites[$index] ?? 1));
                    $prestation->addPrestationProduit($pp);
                    $em->persist($pp);
                }
            }

            $em->flush();

            $this->addFlash('success', 'Prestation modifiée avec succès !');
            return $this->redirectToRoute('app_prestations');
        }

        return $this->render('prestation/edit.html.twig', [
            'prestation' => $prestation,
            'employes' => $employes,
            'produits' => $produits,
        ]);
    }

    #[Route('/{id}/supprimer', name: 'app_prestation_delete', methods: ['POST'])]
    public function delete(Prestation $prestation, EntityManagerInterface $em): Response
    {
        $em->remove($prestation);
        $em->flush();

        $this->addFlash('success', 'Prestation supprimée avec succès !');
        return $this->redirectToRoute('app_prestations');
    }

    #[Route('/{id}/toggle', name: 'app_prestation_toggle', methods: ['POST'])]
    public function toggle(Prestation $prestation, EntityManagerInterface $em): Response
    {
        $prestation->setActif(!$prestation->isActif());
        $em->flush();

        $this->addFlash('success', 'Statut modifié avec succès !');
        return $this->redirectToRoute('app_prestations');
    }
}