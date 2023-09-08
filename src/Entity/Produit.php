<?php

namespace App\Entity;

use Cocur\Slugify\Slugify;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ProduitRepository;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
#[ORM\Table(name: "produit"), ORM\Index(columns: ["nom_produit", "description"], flags: ["fulltext"])]

class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $nomProduit = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    /**
     * @ORM\Column(type="integer")
     * @Assert\GreaterThanOrEqual(
     * value = 0.1,
     *  message = "Le prix  doit être supérieur à {{ compared_value }}.")
     */

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: '2')]
    private ?string $prix = null;


    /**
     * @ORM\Column(type="integer")
     * @Assert\GreaterThanOrEqual(
     * value = 1,
     *  message = "La quantité disponible doit être d'au moins {{ compared_value }}.")
     */
    #[ORM\Column]
    private ?int $disponible = 1;

    #[ORM\ManyToOne(inversedBy: 'produits')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'produits')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Categorie $categorie = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true,   options: ["default" => "CURRENT_TIMESTAMP"])]
    private ?\DateTimeInterface $dateCreationProduit;



    #[ORM\OneToMany(mappedBy: 'produit', targetEntity: Images::class, cascade: ["persist"])]
    private Collection $images;

    #[ORM\Column]
    private ?bool $etat = null;

    #[ORM\Column(length: 100)]
    private ?string $statut = null;

    #[ORM\OneToMany(mappedBy: 'produits', targetEntity: Offre::class)]
    private Collection $offres;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $stripeProductId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $stripePriceId = null;

    #[ORM\Column]
    private ?bool $isSelling = false;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $prixOffre = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $slug = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageShow = 'https://static.zoomalia.com/prod_img/114198/banner_advantage-jouet-knot.jpeg';
    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->offres = new ArrayCollection();
    }

    public function prePersist()
    {
        $this->slug = (new Slugify())->slugify($this->nomProduit);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomProduit(): ?string
    {
        return $this->nomProduit;
    }

    public function setNomProduit(string $nomProduit): self
    {
        $this->nomProduit = $nomProduit;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPrix(): ?string
    {
        return $this->prix;
    }

    public function setPrix(string $prix): self
    {
        $this->prix = $prix;

        return $this;
    }

    public function getDisponible(): ?int
    {
        return $this->disponible;
    }

    public function setDisponible(int $disponible): self
    {
        $this->disponible = $disponible;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCategorie(): ?Categorie
    {
        return $this->categorie;
    }

    public function setCategorie(?Categorie $categorie): self
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getDateCreationProduit(): ?\DateTimeInterface
    {
        return $this->dateCreationProduit;
    }

    public function setDateCreationProduit(\DateTimeInterface $dateCreationProduit): self
    {
        $this->dateCreationProduit = $dateCreationProduit;

        return $this;
    }



    /**
     * @return Collection<int, Images>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Images $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setProduit($this);
        }

        return $this;
    }

    public function removeImage(Images $image): self
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getProduit() === $this) {
                $image->setProduit(null);
            }
        }

        return $this;
    }

    public function isEtat(): ?bool
    {
        return $this->etat;
    }

    public function setEtat(bool $etat): self
    {
        $this->etat = $etat;

        return $this;
    }


    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): self
    {
        $this->statut = $statut;

        return $this;
    }

    public function __toString()
    {
        return $this->nomProduit;
    }

    /**
     * @return Collection<int, Offre>
     */
    public function getOffres(): Collection
    {
        return $this->offres;
    }

    public function addOffre(Offre $offre): self
    {
        if (!$this->offres->contains($offre)) {
            $this->offres->add($offre);
            $offre->setProduits($this);
        }

        return $this;
    }

    public function removeOffre(Offre $offre): self
    {
        if ($this->offres->removeElement($offre)) {
            // set the owning side to null (unless already changed)
            if ($offre->getProduits() === $this) {
                $offre->setProduits(null);
            }
        }

        return $this;
    }

    public function getStripeProductId(): ?string
    {
        return $this->stripeProductId;
    }

    public function setStripeProductId(?string $stripeProductId): self
    {
        $this->stripeProductId = $stripeProductId;

        return $this;
    }

    public function getStripePriceId(): ?string
    {
        return $this->stripePriceId;
    }

    public function setStripePriceId(?string $stripePriceId): self
    {
        $this->stripePriceId = $stripePriceId;

        return $this;
    }

    public function isIsSelling(): ?bool
    {
        return $this->isSelling;
    }

    public function setIsSelling(bool $isSelling): self
    {
        $this->isSelling = $isSelling;

        return $this;
    }

    public function getPrixOffre(): ?string
    {
        return $this->prixOffre;
    }

    public function setPrixOffre(?string $prixOffre): self
    {
        $this->prixOffre = $prixOffre;

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

    public function getImageShow(): ?string
    {
        return $this->imageShow;
    }

    public function setImageShow(?string $imageShow): self
    {
        $this->imageShow = $imageShow;

        return $this;
    }
}
