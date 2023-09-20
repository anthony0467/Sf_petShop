<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ImagesRepository;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ImagesRepository::class)]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => 'image:item']),
        new GetCollection(normalizationContext: ['groups' => 'image:list'])
    ],
    paginationEnabled: false,
)]
class Images
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['image:list', 'image:item'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['image:list', 'image:item'])]
    private ?string $nomImage = null;

    #[ORM\ManyToOne(inversedBy: 'images')]
    #[Groups(['image:list', 'image:item'])]
    private ?Produit $produit = null;

    #[ORM\ManyToOne(inversedBy: 'images')]
    #[Groups(['image:list', 'image:item'])]
    private ?Evenement $evenement = null;

    #[ORM\ManyToOne(inversedBy: 'image')]
    #[Groups(['image:list', 'image:item'])]
    private ?Slider $slider = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomImage(): ?string
    {
        return $this->nomImage;
    }

    public function setNomImage(string $nomImage): self
    {
        $this->nomImage = $nomImage;

        return $this;
    }

    public function getProduit(): ?Produit
    {
        return $this->produit;
    }

    public function setProduit(?Produit $produit): self
    {
        $this->produit = $produit;

        return $this;
    }

    public function getEvenement(): ?Evenement
    {
        return $this->evenement;
    }

    public function setEvenement(?Evenement $evenement): self
    {
        $this->evenement = $evenement;

        return $this;
    }

    public function getSlider(): ?Slider
    {
        return $this->slider;
    }

    public function setSlider(?Slider $slider): self
    {
        $this->slider = $slider;

        return $this;
    }
}
