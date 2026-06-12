<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Salon::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Salon $salon = null;

    #[ORM\Column(length: 150)]
    private ?string $nom = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $categorie = null; // soin, colorant, outil, consommable

    #[ORM\Column]
    private ?int $quantiteStock = 0;

    #[ORM\Column]
    private ?int $seuilAlerte = 5;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $prixAchat = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $prixVente = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int { return $this->id; }

    public function getSalon(): ?Salon { return $this->salon; }
    public function setSalon(?Salon $salon): static { $this->salon = $salon; return $this; }

    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }

    public function getCategorie(): ?string { return $this->categorie; }
    public function setCategorie(?string $categorie): static { $this->categorie = $categorie; return $this; }

    public function getQuantiteStock(): ?int { return $this->quantiteStock; }
    public function setQuantiteStock(int $quantiteStock): static { $this->quantiteStock = $quantiteStock; return $this; }

    public function getSeuilAlerte(): ?int { return $this->seuilAlerte; }
    public function setSeuilAlerte(int $seuilAlerte): static { $this->seuilAlerte = $seuilAlerte; return $this; }

    public function getPrixAchat(): ?string { return $this->prixAchat; }
    public function setPrixAchat(?string $prixAchat): static { $this->prixAchat = $prixAchat; return $this; }

    public function getPrixVente(): ?string { return $this->prixVente; }
    public function setPrixVente(?string $prixVente): static { $this->prixVente = $prixVente; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): static { $this->createdAt = $createdAt; return $this; }
}