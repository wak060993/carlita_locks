<?php

namespace App\Command;

use App\Entity\Rappel;
use App\Repository\RendezVousRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:envoyer-rappels',
    description: 'Envoie les rappels WhatsApp pour les RDV du lendemain',
)]
class EnvoyerRappelsCommand extends Command
{
    public function __construct(
        private RendezVousRepository $rdvRepo,
        private EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Envoi des rappels WhatsApp');

        // RDV de demain
        $demain = new \DateTime('tomorrow');
        $debutDemain = new \DateTime('tomorrow 00:00:00');
        $finDemain = new \DateTime('tomorrow 23:59:59');

        $rdvs = $this->rdvRepo->findRdvPourRappels($debutDemain, $finDemain);

        if (empty($rdvs)) {
            $io->info('Aucun RDV à rappeler demain.');
            return Command::SUCCESS;
        }

        $io->info(count($rdvs) . ' RDV trouvés pour demain.');

        $envoyes = 0;
        $erreurs = 0;

        foreach ($rdvs as $rdv) {
            $client = $rdv->getClient();

            // Vérifier que le client a un numéro WhatsApp
            if (!$client->getWhatsapp()) {
                $io->warning('Client ' . $client->getPrenom() . ' ' . $client->getNom() . ' n\'a pas de WhatsApp');
                continue;
            }

            // Construire le message
            $message = $this->construireMessage($rdv);

            // Envoyer via WPPConnect
            $resultat = $this->envoyerWhatsApp($client->getWhatsapp(), $message);

            // Enregistrer le rappel en base
            $rappel = new Rappel();
            $rappel->setRendezVous($rdv);
            $rappel->setClient($client);
            $rappel->setMessage($message);
            $rappel->setDateEnvoiPrevu($demain);

            if ($resultat) {
                $rappel->setStatut('envoye');
                $rappel->setDateEnvoiReel(new \DateTime());
                $rdv->setRappelEnvoye(true);
                $envoyes++;
                $io->success('✅ Rappel envoyé à ' . $client->getPrenom() . ' ' . $client->getNom());
            } else {
                $rappel->setStatut('echoue');
                $erreurs++;
                $io->error('❌ Échec envoi à ' . $client->getPrenom() . ' ' . $client->getNom());
            }

            $rappel->setCreatedAt(new \DateTimeImmutable());
            $this->em->persist($rappel);
        }

        $this->em->flush();

        $io->success("Rappels envoyés : {$envoyes} | Échecs : {$erreurs}");

        return Command::SUCCESS;
    }

    private function construireMessage($rdv): string
    {
        $client = $rdv->getClient();
        $titre = $client->getTitre() ? $client->getTitre() . ' ' : '';
        $prenom = $client->getPrenom();
        $prestation = $rdv->getPrestation()->getNom();
        $heure = $rdv->getDateHeureDebut()->format('H:i');
        $employe = $rdv->getEmploye()->getPrenom();
        $salon = $rdv->getSalon();

        return "Bonjour {$titre}{$prenom} ! 😊\n\n" .
               "✂️ *Carlita Locks* vous rappelle votre rendez-vous de demain :\n\n" .
               "📅 *Prestation :* {$prestation}\n" .
               "⏰ *Heure :* {$heure}\n" .
               "👩 *Avec :* {$employe}\n\n" .
               "📍 *{$salon->getNom()}*\n" .
               "📞 {$salon->getTelephone()}\n\n" .
               "En cas d'empêchement, merci de nous prévenir au plus tôt.\n\n" .
               "_À demain !_ 🌟";
    }

    private function envoyerWhatsApp(string $telephone, string $message): bool
    {
        try {
            $ch = curl_init('http://localhost:3000/send-message');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                'phone' => $telephone,
                'message' => $message
            ]));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                $data = json_decode($response, true);
                return isset($data['success']) && $data['success'];
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
}