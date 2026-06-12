<?php

namespace App\Entity;

use App\Repository\RendezVousRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RendezVousRepository::class)]
class RendezVous
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Salon::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Salon $salon = null;

    #[ORM\ManyToOne(targetEntity: Client::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Client $client = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $employe = null;

    #[ORM\ManyToOne(targetEntity: Prestation::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Prestation $prestation = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateHeureDebut = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateHeureFin = null;

    #[ORM\Column(length: 20)]
    private ?string $statut = 'en_attente'; // en_attente, confirme, en_cours, termine, annule, no_show

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\Column]
    private ?bool $rappelEnvoye = false;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int { return $this->id; }

    public function getSalon(): ?Salon { return $this->salon; }
    public function setSalon(?Salon $salon): static { $this->salon = $salon; return $this; }

    public function getClient(): ?Client { return $this->client; }
    public function setClient(?Client $client): static { $this->client = $client; return $this; }

    public function getEmploye(): ?Utilisateur { return $this->employe; }
    public function setEmploye(?Utilisateur $employe): static { $this->employe = $employe; return $this; }

    public function getPrestation(): ?Prestation { return $this->prestation; }
    public function setPrestation(?Prestation $prestation): static { $this->prestation = $prestation; return $this; }

    public function getDateHeureDebut(): ?\DateTimeInterface { return $this->dateHeureDebut; }
    public function setDateHeureDebut(\DateTimeInterface $dateHeureDebut): static { $this->dateHeureDebut = $dateHeureDebut; return $this; }

    public function getDateHeureFin(): ?\DateTimeInterface { return $this->dateHeureFin; }
    public function setDateHeureFin(\DateTimeInterface $dateHeureFin): static { $this->dateHeureFin = $dateHeureFin; return $this; }

    public function getStatut(): ?string { return $this->statut; }
    public function setStatut(string $statut): static { $this->statut = $statut; return $this; }

    public function getNotes(): ?string { return $this->notes; }
    public function setNotes(?string $notes): static { $this->notes = $notes; return $this; }

    public function isRappelEnvoye(): ?bool { return $this->rappelEnvoye; }
    public function setRappelEnvoye(bool $rappelEnvoye): static { $this->rappelEnvoye = $rappelEnvoye; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): static { $this->createdAt = $createdAt; return $this; }
}