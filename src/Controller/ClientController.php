<?php

namespace App\Controller;

use App\Entity\Client;
use App\Repository\ClientRepository;
use App\Repository\RendezVousRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/clients')]
class ClientController extends AbstractController
{
    #[Route('/', name: 'app_clients')]
public function index(Request $request, ClientRepository $clientRepository): Response
{
    $search = $request->query->get('search', '');

    if ($search) {
        $clients = $clientRepository->search($search, 1);
    } else {
        $clients = $clientRepository->findBy(['salon' => 1], ['nom' => 'ASC']);
    }

    return $this->render('client/index.html.twig', [
        'clients' => $clients,
        'search' => $search,
    ]);
}

    #[Route('/nouveau', name: 'app_client_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $client = new Client();

        if ($request->isMethod('POST')) {
    $salon = $em->getRepository(\App\Entity\Salon::class)->find(1);
    $client->setSalon($salon);
    $client->setNom($request->request->get('nom'));
            $client->setPrenom($request->request->get('prenom'));
            $client->setTelephone($request->request->get('telephone'));
            $client->setWhatsapp($request->request->get('whatsapp'));
            $client->setNotes($request->request->get('notes'));
            $client->setPointsFidelite(0);
            $client->setCreatedAt(new \DateTimeImmutable());

            $em->persist($client);
            $em->flush();

            $this->addFlash('success', 'Client ajouté avec succès !');
            return $this->redirectToRoute('app_clients');
        }

        return $this->render('client/new.html.twig');
    }

    #[Route('/{id}/modifier', name: 'app_client_edit')]
    public function edit(Client $client, Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $client->setNom($request->request->get('nom'));
            $client->setPrenom($request->request->get('prenom'));
            $client->setTelephone($request->request->get('telephone'));
            $client->setWhatsapp($request->request->get('whatsapp'));
            $client->setNotes($request->request->get('notes'));

            $em->flush();

            $this->addFlash('success', 'Client modifié avec succès !');
            return $this->redirectToRoute('app_clients');
        }

        return $this->render('client/edit.html.twig', [
            'client' => $client,
        ]);
    }

    #[Route('/{id}/supprimer', name: 'app_client_delete', methods: ['POST'])]
    public function delete(Client $client, EntityManagerInterface $em): Response
    {
        $em->remove($client);
        $em->flush();

        $this->addFlash('success', 'Client supprimé avec succès !');
        return $this->redirectToRoute('app_clients');
    }

    #[Route('/{id}/fiche', name: 'app_client_fiche')]
public function fiche(
    Client $client,
    RendezVousRepository $rdvRepository,
    EntityManagerInterface $em
): Response {
    // Historique des RDV
    $rdvs = $rdvRepository->findBy(
        ['client' => $client],
        ['dateHeureDebut' => 'DESC']
    );

    // Statistiques
    $totalDepense = 0;
    $prestationsCount = [];
    $rdvTermines = 0;

    foreach ($rdvs as $rdv) {
        if (in_array($rdv->getStatut(), ['termine', 'encaisse'])) {
            $totalDepense += $rdv->getPrestation()->getPrix();
            $rdvTermines++;
            $nomPrestation = $rdv->getPrestation()->getNom();
            $prestationsCount[$nomPrestation] = ($prestationsCount[$nomPrestation] ?? 0) + 1;
        }
    }

    // Trier les prestations favorites
    arsort($prestationsCount);
    $prestationsFavorites = array_slice($prestationsCount, 0, 3, true);

    return $this->render('client/fiche.html.twig', [
        'client' => $client,
        'rdvs' => $rdvs,
        'total_depense' => $totalDepense,
        'rdv_termines' => $rdvTermines,
        'prestations_favorites' => $prestationsFavorites,
        'rdv_count' => count($rdvs),
    ]);
}
}