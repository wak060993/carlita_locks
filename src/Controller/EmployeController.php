<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/employes')]
class EmployeController extends AbstractController
{
    #[Route('/', name: 'app_employes')]
    public function index(UtilisateurRepository $utilisateurRepository): Response
    {
        $employes = $utilisateurRepository->findAll();
        return $this->render('employe/index.html.twig', [
            'employes' => $employes,
        ]);
    }

    #[Route('/nouveau', name: 'app_employe_new')]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        UtilisateurRepository $utilisateurRepository,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        if ($request->isMethod('POST')) {

            // Vérifier si email déjà utilisé
            $emailExistant = $utilisateurRepository->findOneBy([
                'email' => $request->request->get('email')
            ]);

            if ($emailExistant) {
                $this->addFlash('danger', 'Cet email est déjà utilisé par un autre employé !');
                return $this->render('employe/new.html.twig');
            }

            $employe = new Utilisateur();
            $salon = $em->getRepository(\App\Entity\Salon::class)->find(1);
            $employe->setSalon($salon);
            $employe->setNom($request->request->get('nom'));
            $employe->setPrenom($request->request->get('prenom'));
            $employe->setEmail($request->request->get('email'));
            $employe->setTelephone($request->request->get('telephone'));
            $employe->setRole($request->request->get('role'));
            $employe->setMotDePasse(
                $passwordHasher->hashPassword($employe, $request->request->get('mot_de_passe'))
            );
            $employe->setActif(true);
            $employe->setCreatedAt(new \DateTimeImmutable());

            $em->persist($employe);
            $em->flush();

            $this->addFlash('success', 'Employé(e) ajouté(e) avec succès !');
            return $this->redirectToRoute('app_employes');
        }

        return $this->render('employe/new.html.twig');
    }

    #[Route('/{id}/modifier', name: 'app_employe_edit')]
    public function edit(
        Utilisateur $employe,
        Request $request,
        EntityManagerInterface $em,
        UtilisateurRepository $utilisateurRepository,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        if ($request->isMethod('POST')) {

            // Vérifier si email déjà utilisé par un autre employé
            $emailExistant = $utilisateurRepository->findOneBy([
                'email' => $request->request->get('email')
            ]);

            if ($emailExistant && $emailExistant->getId() !== $employe->getId()) {
                $this->addFlash('danger', 'Cet email est déjà utilisé par un autre employé !');
                return $this->render('employe/edit.html.twig', ['employe' => $employe]);
            }

            $employe->setNom($request->request->get('nom'));
            $employe->setPrenom($request->request->get('prenom'));
            $employe->setEmail($request->request->get('email'));
            $employe->setTelephone($request->request->get('telephone'));
            $employe->setRole($request->request->get('role'));
            $employe->setActif($request->request->get('actif') ? true : false);

            // Mot de passe uniquement si renseigné
            $motDePasse = $request->request->get('mot_de_passe');
            if ($motDePasse && $motDePasse !== '') {
                $employe->setMotDePasse(
                    $passwordHasher->hashPassword($employe, $motDePasse)
                );
            }

            $em->flush();

            $this->addFlash('success', 'Employé(e) modifié(e) avec succès !');
            return $this->redirectToRoute('app_employes');
        }

        return $this->render('employe/edit.html.twig', [
            'employe' => $employe,
        ]);
    }

    #[Route('/{id}/toggle', name: 'app_employe_toggle', methods: ['POST'])]
    public function toggle(Utilisateur $employe, EntityManagerInterface $em): Response
    {
        $employe->setActif(!$employe->isActif());
        $em->flush();

        $this->addFlash('success', 'Statut modifié avec succès !');
        return $this->redirectToRoute('app_employes');
    }

    #[Route('/{id}/supprimer', name: 'app_employe_delete', methods: ['POST'])]
    public function delete(Utilisateur $employe, EntityManagerInterface $em): Response
    {
        $em->remove($employe);
        $em->flush();

        $this->addFlash('success', 'Employé(e) supprimé(e) avec succès !');
        return $this->redirectToRoute('app_employes');
    }
}