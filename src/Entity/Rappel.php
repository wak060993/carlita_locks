<?php

namespace App\Entity;

use App\Repository\RappelRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RappelRepository::class)]
class Rappel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: RendezVous::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?RendezVous $rendezVous = null;

    #[ORM\ManyToOne(targetEntity: Client::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Client $client = null;

    #[ORM\Column(type: 'text')]
    private ?string $message = null;

    #[ORM\Column(length: 20)]
    private ?string $statut = 'en_attente'; // en_attente, envoye, echoue

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateEnvoiPrevu = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $dateEnvoiReel = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int { return $this->id; }

    public function getRendezVous(): ?RendezVous { return $this->rendezVous; }
    public function setRendezVous(?RendezVous $rendezVous): static { $this->rendezVous = $rendezVous; return $this; }

    public function getClient(): ?Client { return $this->client; }
    public function setClient(?Client $client): static { $this->client = $client; return $this; }

    public function getMessage(): ?string { return $this->message; }
    public function setMessage(string $message): static { $this->message = $message; return $this; }

    public function getStatut(): ?string { return $this->statut; }
    public function setStatut(string $statut): static { $this->statut = $statut; return $this; }

    public function getDateEnvoiPrevu(): ?\DateTimeInterface { return $this->dateEnvoiPrevu; }
    public function setDateEnvoiPrevu(\DateTimeInterface $dateEnvoiPrevu): static { $this->dateEnvoiPrevu = $dateEnvoiPrevu; return $this; }

    public function getDateEnvoiReel(): ?\DateTimeInterface { return $this->dateEnvoiReel; }
    public function setDateEnvoiReel(?\DateTimeInterface $dateEnvoiReel): static { $this->dateEnvoiReel = $dateEnvoiReel; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): static { $this->createdAt = $createdAt; return $this; }
}