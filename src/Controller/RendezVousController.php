<?php

namespace App\Controller;

use App\Entity\RendezVous;
use App\Repository\ClientRepository;
use App\Repository\PrestationRepository;
use App\Repository\RendezVousRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/rendez-vous')]
class RendezVousController extends AbstractController
{
    #[Route('/', name: 'app_rendez_vous')]
    public function index(Request $request, RendezVousRepository $rendezVousRepository): Response
    {
        $date = $request->query->get('date', date('Y-m-d'));
        $dateObj = new \DateTime($date);
        $rendezVous = $rendezVousRepository->findByDate($dateObj, 1);

        return $this->render('rendez_vous/index.html.twig', [
            'rendezVous' => $rendezVous,
            'date' => $dateObj,
        ]);
    }

    #[Route('/nouveau', name: 'app_rendez_vous_new')]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        ClientRepository $clientRepository,
        PrestationRepository $prestationRepository,
        UtilisateurRepository $utilisateurRepository,
        RendezVousRepository $rendezVousRepository
    ): Response {
        $clients = $clientRepository->findBy(['salon' => 1]);
        $prestations = $prestationRepository->findBy(['salon' => 1, 'actif' => true]);
        $employes = $utilisateurRepository->findBy(['actif' => true]);

        if ($request->isMethod('POST')) {
            $client = $clientRepository->find($request->request->get('client_id'));
            $prestation = $prestationRepository->find($request->request->get('prestation_id'));
            $employe = $utilisateurRepository->find($request->request->get('employe_id'));

            $dateDebut = new \DateTime($request->request->get('date_heure_debut'));
            $dateFin = clone $dateDebut;
            $dateFin->modify('+' . $prestation->getDureeMinutes() . ' minutes');

            // Détection des conflits
            $conflits = $rendezVousRepository->findConflits(
                $employe->getId(),
                $dateDebut,
                $dateFin
            );

            if (!empty($conflits)) {
                $conflit = $conflits[0];
                $this->addFlash('danger',
                    $employe->getPrenom() . ' ' . $employe->getNom() .
                    ' a déjà un RDV de ' .
                    $conflit->getDateHeureDebut()->format('H:i') .
                    ' à ' .
                    $conflit->getDateHeureFin()->format('H:i') .
                    ' (' . $conflit->getClient()->getPrenom() . ' ' . $conflit->getClient()->getNom() . ')' .
                    '. Veuillez choisir un autre créneau ou une autre employée.'
                );
                return $this->render('rendez_vous/new.html.twig', [
                    'clients' => $clients,
                    'prestations' => $prestations,
                    'employes' => $employes,
                ]);
            }

            $rdv = new RendezVous();
            $salon = $em->getRepository(\App\Entity\Salon::class)->find(1);
            $rdv->setSalon($salon);
            $rdv->setClient($client);
            $rdv->setPrestation($prestation);
            $rdv->setEmploye($employe);
            $rdv->setDateHeureDebut($dateDebut);
            $rdv->setDateHeureFin($dateFin);
            $rdv->setStatut('en_attente');
            $rdv->setNotes($request->request->get('notes'));
            $rdv->setRappelEnvoye(false);
            $rdv->setCreatedAt(new \DateTimeImmutable());

            $em->persist($rdv);
            $em->flush();

            $this->addFlash('success', 'Rendez-vous créé avec succès !');
            return $this->redirectToRoute('app_rendez_vous');
        }

        return $this->render('rendez_vous/new.html.twig', [
            'clients' => $clients,
            'prestations' => $prestations,
            'employes' => $employes,
        ]);
    }

    #[Route('/{id}/statut', name: 'app_rendez_vous_statut', methods: ['POST'])]
    public function updateStatut(RendezVous $rdv, Request $request, EntityManagerInterface $em): Response
    {
        $rdv->setStatut($request->request->get('statut'));
        $em->flush();

        $this->addFlash('success', 'Statut mis à jour !');
        return $this->redirectToRoute('app_rendez_vous');
    }

    #[Route('/{id}/modifier', name: 'app_rendez_vous_edit')]
    public function edit(
        RendezVous $rdv,
        Request $request,
        EntityManagerInterface $em,
        ClientRepository $clientRepository,
        PrestationRepository $prestationRepository,
        UtilisateurRepository $utilisateurRepository,
        RendezVousRepository $rendezVousRepository
    ): Response {
        $clients = $clientRepository->findBy(['salon' => 1]);
        $prestations = $prestationRepository->findBy(['salon' => 1, 'actif' => true]);
        $employes = $utilisateurRepository->findBy(['actif' => true]);

        if ($request->isMethod('POST')) {
            $client = $clientRepository->find($request->request->get('client_id'));
            $prestation = $prestationRepository->find($request->request->get('prestation_id'));
            $employe = $utilisateurRepository->find($request->request->get('employe_id'));

            $dateDebut = new \DateTime($request->request->get('date_heure_debut'));
            $dateFin = clone $dateDebut;
            $dateFin->modify('+' . $prestation->getDureeMinutes() . ' minutes');

            // Détection des conflits en excluant le RDV en cours de modification
            $conflits = $rendezVousRepository->findConflits(
                $employe->getId(),
                $dateDebut,
                $dateFin,
                $rdv->getId()
            );

            if (!empty($conflits)) {
                $conflit = $conflits[0];
                $this->addFlash('danger',
                    $employe->getPrenom() . ' ' . $employe->getNom() .
                    ' a déjà un RDV de ' .
                    $conflit->getDateHeureDebut()->format('H:i') .
                    ' à ' .
                    $conflit->getDateHeureFin()->format('H:i') .
                    ' (' . $conflit->getClient()->getPrenom() . ' ' . $conflit->getClient()->getNom() . ')' .
                    '. Veuillez choisir un autre créneau ou une autre employée.'
                );
                return $this->render('rendez_vous/edit.html.twig', [
                    'rdv' => $rdv,
                    'clients' => $clients,
                    'prestations' => $prestations,
                    'employes' => $employes,
                ]);
            }

            $rdv->setClient($client);
            $rdv->setPrestation($prestation);
            $rdv->setEmploye($employe);
            $rdv->setDateHeureDebut($dateDebut);
            $rdv->setDateHeureFin($dateFin);
            $rdv->setStatut($request->request->get('statut'));
            $rdv->setNotes($request->request->get('notes'));

            $em->flush();

            $this->addFlash('success', 'Rendez-vous modifié avec succès !');
            return $this->redirectToRoute('app_rendez_vous');
        }

        return $this->render('rendez_vous/edit.html.twig', [
            'rdv' => $rdv,
            'clients' => $clients,
            'prestations' => $prestations,
            'employes' => $employes,
        ]);
    }

    #[Route('/{id}/supprimer', name: 'app_rendez_vous_delete', methods: ['POST'])]
    public function delete(RendezVous $rdv, EntityManagerInterface $em): Response
    {
        $em->remove($rdv);
        $em->flush();

        $this->addFlash('success', 'Rendez-vous supprimé avec succès !');
        return $this->redirectToRoute('app_rendez_vous');
    }

    #[Route('/employes-par-prestation/{id}', name: 'app_employes_par_prestation')]
    public function employesParPrestation(int $id, PrestationRepository $prestationRepository): JsonResponse
    {
        $prestation = $prestationRepository->find($id);
        $employes = [];

        if ($prestation) {
            foreach ($prestation->getEmployes() as $employe) {
                $employes[] = [
                    'id' => $employe->getId(),
                    'nom' => $employe->getPrenom() . ' ' . $employe->getNom(),
                ];
            }
        }

        return new JsonResponse($employes);
    }
}