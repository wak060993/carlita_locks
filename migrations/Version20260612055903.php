<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260612055903 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE prestation_employe (prestation_id INT NOT NULL, utilisateur_id INT NOT NULL, INDEX IDX_3B9220209E45C554 (prestation_id), INDEX IDX_3B922020FB88E14F (utilisateur_id), PRIMARY KEY (prestation_id, utilisateur_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE prestation_employe ADD CONSTRAINT FK_3B9220209E45C554 FOREIGN KEY (prestation_id) REFERENCES prestation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE prestation_employe ADD CONSTRAINT FK_3B922020FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C74404554C91BDE4 FOREIGN KEY (salon_id) REFERENCES salon (id)');
        $this->addSql('ALTER TABLE conge_absence ADD CONSTRAINT FK_A6D6BB6A1B65292 FOREIGN KEY (employe_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE facture ADD CONSTRAINT FK_FE8664104C91BDE4 FOREIGN KEY (salon_id) REFERENCES salon (id)');
        $this->addSql('ALTER TABLE facture ADD CONSTRAINT FK_FE8664102FC0CB0F FOREIGN KEY (transaction_id) REFERENCES transaction (id)');
        $this->addSql('ALTER TABLE facture ADD CONSTRAINT FK_FE86641019EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE mouvement_stock ADD CONSTRAINT FK_61E2C8EBF347EFB FOREIGN KEY (produit_id) REFERENCES produit (id)');
        $this->addSql('ALTER TABLE mouvement_stock ADD CONSTRAINT FK_61E2C8EB1B65292 FOREIGN KEY (employe_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE planning_employe ADD CONSTRAINT FK_25698E524C91BDE4 FOREIGN KEY (salon_id) REFERENCES salon (id)');
        $this->addSql('ALTER TABLE planning_employe ADD CONSTRAINT FK_25698E521B65292 FOREIGN KEY (employe_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE prestation ADD categorie VARCHAR(50) DEFAULT NULL, ADD commission_pourcentage NUMERIC(5, 2) DEFAULT NULL');
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
        $this->addSql('ALTER TABLE prestation_employe DROP FOREIGN KEY FK_3B9220209E45C554');
        $this->addSql('ALTER TABLE prestation_employe DROP FOREIGN KEY FK_3B922020FB88E14F');
        $this->addSql('DROP TABLE prestation_employe');
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
        $this->addSql('ALTER TABLE prestation DROP categorie, DROP commission_pourcentage');
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
    }
}
