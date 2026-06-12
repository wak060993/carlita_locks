<?php

namespace App\DataFixtures;

use App\Entity\Salon;
use App\Entity\Utilisateur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        // Création du salon Carlita Locks
        $salon = new Salon();
        $salon->setNom('Carlita Locks');
        $salon->setAdresse('Lomé, Togo');
        $salon->setTelephone('+228 90 58 33 50');
        $salon->setWhatsapp('+228 90 58 33 50');
        $salon->setActif(true);
        $salon->setCreatedAt(new \DateTimeImmutable());
        $manager->persist($salon);

        // Création du gérant principal
        $gerant = new Utilisateur();
        $gerant->setSalon($salon);
        $gerant->setNom('Locks');
        $gerant->setPrenom('Carlita');
        $gerant->setEmail('admin@carlitalocks.com');
        $gerant->setMotDePasse(
            $this->passwordHasher->hashPassword($gerant, 'admin123')
        );
        $gerant->setRole('gerant');
        $gerant->setActif(true);
        $gerant->setCreatedAt(new \DateTimeImmutable());
        $manager->persist($gerant);

        $manager->flush();

        echo "✅ Salon et gérant créés avec succès !\n";
        echo "📧 Email : admin@carlitalocks.com\n";
        echo "🔑 Mot de passe : admin123\n";
    }
}