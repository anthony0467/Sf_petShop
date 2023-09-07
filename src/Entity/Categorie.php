<?php

namespace App\Entity;

use Cocur\Slugify\Slugify;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\CategorieRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: CategorieRepository::class)]
class Categorie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $nomCategorie = null;

    #[ORM\OneToMany(mappedBy: 'categorie', targetEntity: Produit::class, orphanRemoval: true)]
    private Collection $produits;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageCategorie = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $slug = null;

    public function __construct()
    {
        $this->produits = new ArrayCollection();
    }

    public function prePersist()
    {
        $this->slug = (new Slugify())->slugify($this->nomCategorie);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomCategorie(): ?string
    {
        return $this->nomCategorie;
    }

    public function setNomCategorie(string $nomCategorie): self
    {
        $this->nomCategorie = $nomCategorie;

        return $this;
    }

    /**
     * @return Collection<int, Produit>
     */
    public function getProduits(): Collection
    {
        return $this->produits;
    }

    public function addProduit(Produit $produit): self
    {
        if (!$this->produits->contains($produit)) {
            $this->produits->add($produit);
            $produit->setCategorie($this);
        }

        return $this;
    }

    public function removeProduit(Produit $produit): self
    {
        if ($this->produits->removeElement($produit)) {
            // set the owning side to null (unless already changed)
            if ($produit->getCategorie() === $this) {
                $produit->setCategorie(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->nomCategorie;
    }

    public function getImageCategorie(): ?string
    {
        return $this->imageCategorie;
    }

    public function setImageCategorie(?string $imageCategorie): self
    {
        $this->imageCategorie = $imageCategorie;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }
}
