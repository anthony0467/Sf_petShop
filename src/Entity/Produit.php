<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ProduitRepository;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
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

      /**
      * @Vich\UploadableField(mapping="produit_image", fileNameProperty="imageName")
      * @var File|null
      */
      private $imageFile;

      /**
       * @ORM\Column(type="string", length=255, nullable=true)
       * @var string|null
       */
      private $imageName;

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

    public function setImageFile(?File $image = null): void
    {
        $this->imageFile = $image;
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageName(?string $imageName): void
    {
        $this->imageName = $imageName;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }
}
