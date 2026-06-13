<?php

namespace App\Controller;

use App\Entity\Facture;
use App\Entity\Transaction;
use App\Repository\FactureRepository;
use App\Repository\RendezVousRepository;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/caisse')]
class CaisseController extends AbstractController
{
    #[Route('/', name: 'app_caisse')]
    public function index(): Response
    {
        return $this->render('caisse/index.html.twig');
    }

    #[Route('/api/rdv-termines', name: 'api_rdv_termines')]
    public function getRdvTermines(RendezVousRepository $rdvRepo): JsonResponse
    {
        $rdvs = $rdvRepo->findRdvAEncaisser(1);
        $data = [];

        foreach ($rdvs as $rdv) {
            $data[] = [
                'id' => $rdv->getId(),
                'client' => $rdv->getClient()->getPrenom() . ' ' . $rdv->getClient()->getNom(),
                'prestation' => $rdv->getPrestation()->getNom(),
                'employe' => $rdv->getEmploye()->getPrenom() . ' ' . $rdv->getEmploye()->getNom(),
                'prix' => $rdv->getPrestation()->getPrix(),
                'heure' => $rdv->getDateHeureDebut()->format('H:i'),
                'statut' => $rdv->getStatut(),
            ];
        }

        return new JsonResponse($data);
    }

#[Route('/api/encaisser', name: 'api_encaisser', methods: ['POST'])]
public function encaisser(Request $request, EntityManagerInterface $em, RendezVousRepository $rdvRepo): JsonResponse
{
    $data = json_decode($request->getContent(), true);

    $rdv = $rdvRepo->find($data['rdv_id']);
    if (!$rdv) {
        return new JsonResponse(['error' => 'RDV introuvable'], 404);
    }

    $salon = $em->getRepository(\App\Entity\Salon::class)->find(1);

    // Calcul remise
    $prixInitial = floatval($data['prix_initial'] ?? $rdv->getPrestation()->getPrix());
    $typeRemise = $data['type_remise'] ?? '';
    $valeurRemise = floatval($data['valeur_remise'] ?? 0);
    $montantRemise = 0;

    if ($typeRemise === 'pourcentage' && $valeurRemise > 0) {
        $montantRemise = $prixInitial * $valeurRemise / 100;
    } elseif ($typeRemise === 'montant' && $valeurRemise > 0) {
        $montantRemise = $valeurRemise;
    }

    $netAPayer = $prixInitial - $montantRemise;
    $montantPaye = floatval($data['montant']);
    $resteAPayer = max(0, $netAPayer - $montantPaye);

    // Créer la transaction
    $transaction = new Transaction();
    $transaction->setSalon($salon);
    $transaction->setRendezVous($rdv);
    $transaction->setEmploye($rdv->getEmploye());
    $transaction->setType('entree');
    $transaction->setCategorie('prestation');
    $transaction->setMontant($montantPaye);
    $transaction->setModePaiement($data['mode_paiement']);

    $description = 'Paiement : ' . $rdv->getPrestation()->getNom();
    if ($montantRemise > 0) {
        $description .= ' (Remise : ' . number_format($montantRemise, 0, ',', ' ') . ' FCFA)';
    }
    if ($resteAPayer > 0) {
        $description .= ' — Reste à payer : ' . number_format($resteAPayer, 0, ',', ' ') . ' FCFA';
    }
    $transaction->setDescription($description);
    $transaction->setCreatedAt(new \DateTimeImmutable());
    $em->persist($transaction);

    // Créer la facture
    $facture = new Facture();
    $facture->setSalon($salon);
    $facture->setTransaction($transaction);
    $facture->setClient($rdv->getClient());
    $facture->setNumeroFacture('FAC-' . date('Ymd') . '-' . rand(1000, 9999));
    $facture->setMontantTotal($netAPayer);
$facture->setStatut('payee');
    $facture->setCreatedAt(new \DateTimeImmutable());

    // Stocker les infos de remise
    if ($montantRemise > 0) {
        $facture->setNotes(json_encode([
            'prix_initial' => $prixInitial,
            'type_remise' => $typeRemise,
            'valeur_remise' => $valeurRemise,
            'montant_remise' => $montantRemise,
        ]));
    }

    $em->persist($facture);

    // Marquer le RDV
    $rdv->setStatut('encaisse');
    $em->flush();

    return new JsonResponse([
        'success' => true,
        'message' => 'Paiement enregistré avec succès !',
        'facture_id' => $facture->getId(),
        'facture' => $facture->getNumeroFacture(),
        'montant' => $montantPaye,
        'net_a_payer' => $netAPayer,
        'reste_a_payer' => $resteAPayer,
        'client' => $rdv->getClient()->getPrenom() . ' ' . $rdv->getClient()->getNom(),
    ]);
}

    #[Route('/api/transaction-manuelle', name: 'api_transaction_manuelle', methods: ['POST'])]
    public function transactionManuelle(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $salon = $em->getRepository(\App\Entity\Salon::class)->find(1);
        $user = $this->getUser();

        $transaction = new Transaction();
        $transaction->setSalon($salon);
        $transaction->setEmploye($user);
        $transaction->setType($data['type']);
        $transaction->setCategorie($data['categorie']);
        $transaction->setMontant($data['montant']);
        $transaction->setModePaiement($data['mode_paiement'] ?? 'especes');
        $transaction->setDescription($data['description']);
        $transaction->setCreatedAt(new \DateTimeImmutable());
        $em->persist($transaction);

        // Créer une facture uniquement pour les entrées
        $factureId = null;
        if ($data['type'] === 'entree') {
            $facture = new Facture();
            $facture->setSalon($salon);
            $facture->setTransaction($transaction);

            // Chercher un client si fourni
            $client = null;
            if (!empty($data['client_id'])) {
                $client = $em->getRepository(\App\Entity\Client::class)->find($data['client_id']);
            }

            // Si pas de client on prend le premier client par défaut
            if (!$client) {
                $client = $em->getRepository(\App\Entity\Client::class)->findOneBy(['salon' => $salon]);
            }

            $facture->setClient($client);
            $facture->setNumeroFacture('FAC-' . date('Ymd') . '-' . rand(1000, 9999));
            $facture->setMontantTotal($data['montant']);
            $facture->setStatut('payee');
            $facture->setCreatedAt(new \DateTimeImmutable());
            $em->persist($facture);
            $em->flush();
            $factureId = $facture->getId();
        } else {
            $em->flush();
        }

        return new JsonResponse([
            'success' => true,
            'message' => 'Transaction enregistrée !',
            'facture_id' => $factureId,
            'type' => $data['type'],
        ]);
    }

    #[Route('/api/rapport-journalier', name: 'api_rapport_journalier')]
    public function rapportJournalier(TransactionRepository $transactionRepo): JsonResponse
    {
        $rapport = $transactionRepo->getRapportJournalier(1);
        return new JsonResponse($rapport);
    }

    #[Route('/api/vente-produit', name: 'api_vente_produit', methods: ['POST'])]
public function vendProduit(
    Request $request,
    EntityManagerInterface $em,
    \App\Repository\ProduitRepository $produitRepo
): JsonResponse {
    $data = json_decode($request->getContent(), true);
    $salon = $em->getRepository(\App\Entity\Salon::class)->find(1);
    $user = $this->getUser();

    $produit = $produitRepo->find($data['produit_id']);
    if (!$produit) {
        return new JsonResponse(['error' => 'Produit introuvable'], 404);
    }

    $quantite = (int) $data['quantite'];

    // Vérifier stock suffisant
    if ($produit->getQuantiteStock() < $quantite) {
        return new JsonResponse([
            'error' => 'Stock insuffisant. Stock actuel : ' . $produit->getQuantiteStock()
        ], 400);
    }

    $montant = floatval($data['montant']);

    // Créer la transaction
    $transaction = new Transaction();
    $transaction->setSalon($salon);
    $transaction->setEmploye($user);
    $transaction->setType('entree');
    $transaction->setCategorie('vente_produit');
    $transaction->setMontant($montant);
    $transaction->setModePaiement($data['mode_paiement'] ?? 'especes');
    $transaction->setDescription('Vente : ' . $produit->getNom() . ' x' . $quantite);
    $transaction->setCreatedAt(new \DateTimeImmutable());
    $em->persist($transaction);

    // Créer la facture
    $client = null;
    if (!empty($data['client_id'])) {
        $client = $em->getRepository(\App\Entity\Client::class)->find($data['client_id']);
    }
    if (!$client) {
        $client = $em->getRepository(\App\Entity\Client::class)->findOneBy(['salon' => $salon]);
    }

    $facture = new Facture();
    $facture->setSalon($salon);
    $facture->setTransaction($transaction);
    $facture->setClient($client);
    $facture->setNumeroFacture('FAC-' . date('Ymd') . '-' . rand(1000, 9999));
    $facture->setMontantTotal($montant);
    $facture->setStatut('payee');
    $facture->setCreatedAt(new \DateTimeImmutable());
    $em->persist($facture);

    // Déduire du stock
    $produit->setQuantiteStock($produit->getQuantiteStock() - $quantite);

    // Enregistrer le mouvement
    $mouvement = new \App\Entity\MouvementStock();
    $mouvement->setProduit($produit);
    $mouvement->setEmploye($user);
    $mouvement->setType('sortie');
    $mouvement->setQuantite($quantite);
    $mouvement->setMotif('vente');
    $mouvement->setCreatedAt(new \DateTimeImmutable());
    $em->persist($mouvement);

    $em->flush();

    return new JsonResponse([
        'success' => true,
        'message' => 'Vente enregistrée !',
        'facture_id' => $facture->getId(),
        'type' => 'entree',
        'stock_restant' => $produit->getQuantiteStock(),
    ]);
}

#[Route('/api/produits', name: 'api_produits_liste')]
public function getProduits(\App\Repository\ProduitRepository $produitRepo): JsonResponse
{
    $produits = $produitRepo->findBy(['salon' => 1]);
    $data = [];
    foreach ($produits as $produit) {
        $data[] = [
            'id' => $produit->getId(),
            'nom' => $produit->getNom(),
            'prix_vente' => $produit->getPrixVente(),
            'stock' => $produit->getQuantiteStock(),
            'categorie' => $produit->getCategorie(),
        ];
    }
    return new JsonResponse($data);
}

   #[Route('/facture/{id}/recu', name: 'app_recu_facture')]
public function recu(int $id, FactureRepository $factureRepository): Response
{
    $facture = $factureRepository->find($id);

    if (!$facture) {
        throw $this->createNotFoundException('Facture introuvable');
    }

    $remiseData = null;
    if ($facture->getNotes()) {
        $remiseData = json_decode($facture->getNotes(), true);
    }

    return $this->render('caisse/recu.html.twig', [
        'facture' => $facture,
        'remiseData' => $remiseData,
    ]);
}
}