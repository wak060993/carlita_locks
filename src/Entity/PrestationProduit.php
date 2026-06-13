<?php

namespace App\Entity;

use App\Repository\PrestationProduitRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PrestationProduitRepository::class)]
class PrestationProduit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Prestation::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Prestation $prestation = null;

    #[ORM\ManyToOne(targetEntity: Produit::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Produit $produit = null;

    #[ORM\Column]
    private ?int $quantiteUtilisee = 1;

    public function getId(): ?int { return $this->id; }

    public function getPrestation(): ?Prestation { return $this->prestation; }
    public function setPrestation(?Prestation $prestation): static { $this->prestation = $prestation; return $this; }

    public function getProduit(): ?Produit { return $this->produit; }
    public function setProduit(?Produit $produit): static { $this->produit = $produit; return $this; }

    public function getQuantiteUtilisee(): ?int { return $this->quantiteUtilisee; }
    public function setQuantiteUtilisee(int $quantiteUtilisee): static { $this->quantiteUtilisee = $quantiteUtilisee; return $this; }
}