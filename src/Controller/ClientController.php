<?php

namespace App\Controller;

use App\Entity\Client;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/clients')]
class ClientController extends AbstractController
{
    #[Route('/', name: 'app_clients')]
    public function index(ClientRepository $clientRepository): Response
    {
        $clients = $clientRepository->findAll();
        return $this->render('client/index.html.twig', [
            'clients' => $clients,
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
}