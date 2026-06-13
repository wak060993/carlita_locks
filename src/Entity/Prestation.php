<?php

namespace App\Entity;

use App\Repository\PrestationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PrestationRepository::class)]
class Prestation
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
    private ?string $categorie = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?int $dureeMinutes = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?string $prix = null;

    #[ORM\Column]
    private ?bool $actif = true;

    #[ORM\ManyToMany(targetEntity: Utilisateur::class)]
    #[ORM\JoinTable(name: 'prestation_employe')]
    private Collection $employes;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2, nullable: true)]
    private ?string $commissionPourcentage = null;

    #[ORM\OneToMany(targetEntity: PrestationProduit::class, mappedBy: 'prestation', cascade: ['persist', 'remove'])]
    private Collection $prestationProduits;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->employes = new ArrayCollection();
        $this->prestationProduits = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getSalon(): ?Salon { return $this->salon; }
    public function setSalon(?Salon $salon): static { $this->salon = $salon; return $this; }

    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }

    public function getCategorie(): ?string { return $this->categorie; }
    public function setCategorie(?string $categorie): static { $this->categorie = $categorie; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }

    public function getDureeMinutes(): ?int { return $this->dureeMinutes; }
    public function setDureeMinutes(int $dureeMinutes): static { $this->dureeMinutes = $dureeMinutes; return $this; }

    public function getPrix(): ?string { return $this->prix; }
    public function setPrix(string $prix): static { $this->prix = $prix; return $this; }

    public function isActif(): ?bool { return $this->actif; }
    public function setActif(bool $actif): static { $this->actif = $actif; return $this; }

    public function getEmployes(): Collection { return $this->employes; }

    public function addEmploye(Utilisateur $employe): static
    {
        if (!$this->employes->contains($employe)) {
            $this->employes->add($employe);
        }
        return $this;
    }

    public function removeEmploye(Utilisateur $employe): static
    {
        $this->employes->removeElement($employe);
        return $this;
    }

    public function getCommissionPourcentage(): ?string { return $this->commissionPourcentage; }
    public function setCommissionPourcentage(?string $commissionPourcentage): static
    {
        $this->commissionPourcentage = $commissionPourcentage;
        return $this;
    }

    public function getPrestationProduits(): Collection { return $this->prestationProduits; }

    public function addPrestationProduit(PrestationProduit $pp): static
    {
        if (!$this->prestationProduits->contains($pp)) {
            $this->prestationProduits->add($pp);
            $pp->setPrestation($this);
        }
        return $this;
    }

    public function removePrestationProduit(PrestationProduit $pp): static
    {
        if ($this->prestationProduits->removeElement($pp)) {
            if ($pp->getPrestation() === $this) {
                $pp->setPrestation(null);
            }
        }
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): static { $this->createdAt = $createdAt; return $this; }
}