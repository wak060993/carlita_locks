<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260612004911 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE client (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, prenom VARCHAR(100) NOT NULL, telephone VARCHAR(20) DEFAULT NULL, whatsapp VARCHAR(20) DEFAULT NULL, date_naissance DATETIME DEFAULT NULL, notes LONGTEXT DEFAULT NULL, points_fidelite INT NOT NULL, created_at DATETIME NOT NULL, salon_id INT NOT NULL, INDEX IDX_C74404554C91BDE4 (salon_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE conge_absence (id INT AUTO_INCREMENT NOT NULL, date_debut DATE NOT NULL, date_fin DATE NOT NULL, motif VARCHAR(255) DEFAULT NULL, statut VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, employe_id INT NOT NULL, INDEX IDX_A6D6BB6A1B65292 (employe_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE facture (id INT AUTO_INCREMENT NOT NULL, numero_facture VARCHAR(50) NOT NULL, montant_total NUMERIC(10, 2) NOT NULL, statut VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, salon_id INT NOT NULL, transaction_id INT NOT NULL, client_id INT NOT NULL, UNIQUE INDEX UNIQ_FE86641038D27AB1 (numero_facture), INDEX IDX_FE8664104C91BDE4 (salon_id), INDEX IDX_FE8664102FC0CB0F (transaction_id), INDEX IDX_FE86641019EB6921 (client_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE mouvement_stock (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(10) NOT NULL, quantite INT NOT NULL, motif VARCHAR(30) DEFAULT NULL, created_at DATETIME NOT NULL, produit_id INT NOT NULL, employe_id INT NOT NULL, INDEX IDX_61E2C8EBF347EFB (produit_id), INDEX IDX_61E2C8EB1B65292 (employe_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE planning_employe (id INT AUTO_INCREMENT NOT NULL, jour_semaine VARCHAR(10) NOT NULL, heure_debut TIME NOT NULL, heure_fin TIME NOT NULL, actif TINYINT NOT NULL, salon_id INT NOT NULL, employe_id INT NOT NULL, INDEX IDX_25698E524C91BDE4 (salon_id), INDEX IDX_25698E521B65292 (employe_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE prestation (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(150) NOT NULL, description LONGTEXT DEFAULT NULL, duree_minutes INT NOT NULL, prix NUMERIC(10, 2) NOT NULL, actif TINYINT NOT NULL, created_at DATETIME NOT NULL, salon_id INT NOT NULL, INDEX IDX_51C88FAD4C91BDE4 (salon_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE produit (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(150) NOT NULL, categorie VARCHAR(50) DEFAULT NULL, quantite_stock INT NOT NULL, seuil_alerte INT NOT NULL, prix_achat NUMERIC(10, 2) DEFAULT NULL, prix_vente NUMERIC(10, 2) DEFAULT NULL, created_at DATETIME NOT NULL, salon_id INT NOT NULL, INDEX IDX_29A5EC274C91BDE4 (salon_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE rappel (id INT AUTO_INCREMENT NOT NULL, message LONGTEXT NOT NULL, statut VARCHAR(20) NOT NULL, date_envoi_prevu DATETIME NOT NULL, date_envoi_reel DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, rendez_vous_id INT NOT NULL, client_id INT NOT NULL, INDEX IDX_303A29C991EF7EAA (rendez_vous_id), INDEX IDX_303A29C919EB6921 (client_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE rendez_vous (id INT AUTO_INCREMENT NOT NULL, date_heure_debut DATETIME NOT NULL, date_heure_fin DATETIME NOT NULL, statut VARCHAR(20) NOT NULL, notes LONGTEXT DEFAULT NULL, rappel_envoye TINYINT NOT NULL, created_at DATETIME NOT NULL, salon_id INT NOT NULL, client_id INT NOT NULL, employe_id INT NOT NULL, prestation_id INT NOT NULL, INDEX IDX_65E8AA0A4C91BDE4 (salon_id), INDEX IDX_65E8AA0A19EB6921 (client_id), INDEX IDX_65E8AA0A1B65292 (employe_id), INDEX IDX_65E8AA0A9E45C554 (prestation_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE salon (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(150) NOT NULL, adresse VARCHAR(255) DEFAULT NULL, telephone VARCHAR(20) DEFAULT NULL, whatsapp VARCHAR(20) DEFAULT NULL, logo VARCHAR(255) DEFAULT NULL, actif TINYINT NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE transaction (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(10) NOT NULL, categorie VARCHAR(30) NOT NULL, montant NUMERIC(10, 2) NOT NULL, mode_paiement VARCHAR(20) NOT NULL, description VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, salon_id INT NOT NULL, rendez_vous_id INT DEFAULT NULL, employe_id INT NOT NULL, INDEX IDX_723705D14C91BDE4 (salon_id), INDEX IDX_723705D191EF7EAA (rendez_vous_id), INDEX IDX_723705D11B65292 (employe_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, prenom VARCHAR(100) NOT NULL, email VARCHAR(180) NOT NULL, mot_de_passe VARCHAR(255) NOT NULL, telephone VARCHAR(20) DEFAULT NULL, role VARCHAR(20) NOT NULL, actif TINYINT NOT NULL, created_at DATETIME NOT NULL, salon_id INT NOT NULL, UNIQUE INDEX UNIQ_1D1C63B3E7927C74 (email), INDEX IDX_1D1C63B34C91BDE4 (salon_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C74404554C91BDE4 FOREIGN KEY (salon_id) REFERENCES salon (id)');
        $this->addSql('ALTER TABLE conge_absence ADD CONSTRAINT FK_A6D6BB6A1B65292 FOREIGN KEY (employe_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE facture ADD CONSTRAINT FK_FE8664104C91BDE4 FOREIGN KEY (salon_id) REFERENCES salon (id)');
        $this->addSql('ALTER TABLE facture ADD CONSTRAINT FK_FE8664102FC0CB0F FOREIGN KEY (transaction_id) REFERENCES transaction (id)');
        $this->addSql('ALTER TABLE facture ADD CONSTRAINT FK_FE86641019EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE mouvement_stock ADD CONSTRAINT FK_61E2C8EBF347EFB FOREIGN KEY (produit_id) REFERENCES produit (id)');
        $this->addSql('ALTER TABLE mouvement_stock ADD CONSTRAINT FK_61E2C8EB1B65292 FOREIGN KEY (employe_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE planning_employe ADD CONSTRAINT FK_25698E524C91BDE4 FOREIGN KEY (salon_id) REFERENCES salon (id)');
        $this->addSql('ALTER TABLE planning_employe ADD CONSTRAINT FK_25698E521B65292 FOREIGN KEY (employe_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE prestation ADD CONSTRAINT FK_51C88FAD4C91BDE4 FOREIGN KEY (salon_id) REFERENCES salon (id)');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC274C91BDE4 FOREIGN KEY (salon_id) REFERENCES salon (id)');
        $this->addSql('ALTER TABLE rappel ADD CONSTRAINT FK_303A29C991EF7EAA FOREIGN KEY (rendez_vous_id) REFERENCES rendez_vous (id)');
        $this->addSql('ALTER TABLE rappel ADD CONSTRAINT FK_303A29C919EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE rendez_vous ADD CONSTRAINT FK_65E8AA0A4C91BDE4 FOREIGN KEY (salon_id) REFERENCES salon (id)');
        $this->addSql('ALTER TABLE rendez_vous ADD CONSTRAINT FK_65E8AA0A19EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE rendez_vous ADD CONSTRAINT FK_65E8AA0A1B65292 FOREIGN KEY (employe_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE rendez_vous ADD CONSTRAINT FK_65E8AA0A9E45C554 FOREIGN KEY (prestation_id) REFERENCES prestation (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D14C91BDE4 FOREIGN KEY (salon_id) REFERENCES salon (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D191EF7EAA FOREIGN KEY (rendez_vous_id) REFERENCES rendez_vous (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D11B65292 FOREIGN KEY (employe_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE utilisateur ADD CONSTRAINT FK_1D1C63B34C91BDE4 FOREIGN KEY (salon_id) REFERENCES salon (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client DROP FOREIGN KEY FK_C74404554C91BDE4');
        $this->addSql('ALTER TABLE conge_absence DROP FOREIGN KEY FK_A6D6BB6A1B65292');
        $this->addSql('ALTER TABLE facture DROP FOREIGN KEY FK_FE8664104C91BDE4');
        $this->addSql('ALTER TABLE facture DROP FOREIGN KEY FK_FE8664102FC0CB0F');
        $this->addSql('ALTER TABLE facture DROP FOREIGN KEY FK_FE86641019EB6921');
        $this->addSql('ALTER TABLE mouvement_stock DROP FOREIGN KEY FK_61E2C8EBF347EFB');
        $this->addSql('ALTER TABLE mouvement_stock DROP FOREIGN KEY FK_61E2C8EB1B65292');
        $this->addSql('ALTER TABLE planning_employe DROP FOREIGN KEY FK_25698E524C91BDE4');
        $this->addSql('ALTER TABLE planning_employe DROP FOREIGN KEY FK_25698E521B65292');
        $this->addSql('ALTER TABLE prestation DROP FOREIGN KEY FK_51C88FAD4C91BDE4');
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC274C91BDE4');
        $this->addSql('ALTER TABLE rappel DROP FOREIGN KEY FK_303A29C991EF7EAA');
        $this->addSql('ALTER TABLE rappel DROP FOREIGN KEY FK_303A29C919EB6921');
        $this->addSql('ALTER TABLE rendez_vous DROP FOREIGN KEY FK_65E8AA0A4C91BDE4');
        $this->addSql('ALTER TABLE rendez_vous DROP FOREIGN KEY FK_65E8AA0A19EB6921');
        $this->addSql('ALTER TABLE rendez_vous DROP FOREIGN KEY FK_65E8AA0A1B65292');
        $this->addSql('ALTER TABLE rendez_vous DROP FOREIGN KEY FK_65E8AA0A9E45C554');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D14C91BDE4');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D191EF7EAA');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D11B65292');
        $this->addSql('ALTER TABLE utilisateur DROP FOREIGN KEY FK_1D1C63B34C91BDE4');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE conge_absence');
        $this->addSql('DROP TABLE facture');
        $this->addSql('DROP TABLE mouvement_stock');
        $this->addSql('DROP TABLE planning_employe');
        $this->addSql('DROP TABLE prestation');
        $this->addSql('DROP TABLE produit');
        $this->addSql('DROP TABLE rappel');
        $this->addSql('DROP TABLE rendez_vous');
        $this->addSql('DROP TABLE salon');
        $this->addSql('DROP TABLE transaction');
        $this->addSql('DROP TABLE utilisateur');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
