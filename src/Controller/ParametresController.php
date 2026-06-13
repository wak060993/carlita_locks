<?php

namespace App\Controller;

use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/parametres')]
class ParametresController extends AbstractController
{
    #[Route('/salon', name: 'app_parametres_salon')]
    public function salon(Request $request, EntityManagerInterface $em): Response
    {
        $salon = $em->getRepository(\App\Entity\Salon::class)->find(1);

        if ($request->isMethod('POST')) {
            $salon->setNom($request->request->get('nom'));
            $salon->setAdresse($request->request->get('adresse'));
            $salon->setTelephone($request->request->get('telephone'));
            $salon->setWhatsapp($request->request->get('whatsapp'));

            // Upload logo
            $logoFile = $request->files->get('logo');
            if ($logoFile) {
                $logoNom = 'logo_' . uniqid() . '.' . $logoFile->getClientOriginalExtension();
                $logoFile->move($this->getParameter('kernel.project_dir') . '/public/uploads/', $logoNom);
                $salon->setLogo($logoNom);
            }

            $em->flush();
            $this->addFlash('success', 'Informations du salon mises à jour !');
            return $this->redirectToRoute('app_parametres_salon');
        }

        return $this->render('parametres/salon.html.twig', [
            'salon' => $salon,
        ]);
    }

    #[Route('/horaires', name: 'app_parametres_horaires')]
    public function horaires(): Response
    {
        return $this->render('parametres/horaires.html.twig');
    }
}