<?php

namespace App\Entity;

use App\Entity\Produit;
use App\Repository\ContenuPanierRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContenuPanierRepository::class)]
class ContenuPanier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Produit::class, inversedBy: 'contenuPaniers')]
    #[ORM\JoinColumn(nullable: false)]
    private $produit;

    #[ORM\ManyToOne(targetEntity: Panier::class, inversedBy: 'contenuPaniers')]
    #[ORM\JoinColumn(nullable: false)]
    private $panier;

    #[ORM\Column(type: 'integer')]
    private $quantite;

    #[ORM\Column(type: 'datetime')]
    private $date;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduit(): ?produit
    {
        return $this->produit;
    }

    public function setProduit(?produit $produit): self
    {
        $this->produit = $produit;

        return $this;
    }

    public function getPanier(): ?panier
    {
        return $this->panier;
    }

    public function setPanier(?panier $panier): self
    {
        $this->panier = $panier;

        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): self
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getMontant(){

        $produit = $this->produit;
        $quantite = $this->quantite;
        $prix = $produit->getPrix();
        $montant = $quantite * $prix;
      
        return $montant;
    }
}
