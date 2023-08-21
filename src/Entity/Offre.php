<?php

namespace App\Entity;

use App\Repository\OffreRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OffreRepository::class)]
class Offre
{
    public const STATUT_EN_ATTENTE = 0;
    public const STATUT_ACCEPTEE = 1;
    public const STATUT_REFUSEE = 2;

    public function getStatutText(): string
    {
        switch ($this->statut) {
            case self::STATUT_EN_ATTENTE:
                return 'En attente';
            case self::STATUT_ACCEPTEE:
                return 'Acceptée';
            case self::STATUT_REFUSEE:
                return 'Refusée';
            default:
                return 'Inconnu';
        }
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $prix = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, options: ["default" => "CURRENT_TIMESTAMP"])]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column]
    private ?int $statut = null;

    #[ORM\ManyToOne(inversedBy: 'offres')]
    private ?Produit $produits = null;

    #[ORM\ManyToOne(inversedBy: 'offres')]
    private ?User $Users = null;

    #[ORM\Column]
    private ?bool $notifStatus = null;

    #[ORM\Column(length: 200)]
    private ?string $notifMessage = 'Offre en cours de traitement';

    #[ORM\Column]
    private ?bool $isRead = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrix(): ?int
    {
        return $this->prix;
    }

    public function setPrix(int $prix): self
    {
        $this->prix = $prix;

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

    public function getStatut(): ?int
    {
        return $this->statut;
    }

    public function setStatut(int $statut): self
    {
        $this->statut = $statut;

        return $this;
    }

    public function getProduits(): ?Produit
    {
        return $this->produits;
    }

    public function setProduits(?Produit $produits): self
    {
        $this->produits = $produits;

        return $this;
    }

    public function getUsers(): ?User
    {
        return $this->Users;
    }

    public function setUsers(?User $Users): self
    {
        $this->Users = $Users;

        return $this;
    }

    public function isNotifStatus(): ?bool
    {
        return $this->notifStatus;
    }

    public function setNotifStatus(bool $notifStatus): self
    {
        $this->notifStatus = $notifStatus;

        return $this;
    }

    public function getNotifMessage(): ?string
    {
        return $this->notifMessage;
    }

    public function setNotifMessage(string $notifMessage): self
    {
        $this->notifMessage = $notifMessage;

        return $this;
    }

    public function isIsRead(): ?bool
    {
        return $this->isRead;
    }

    public function setIsRead(bool $isRead): self
    {
        $this->isRead = $isRead;

        return $this;
    }
}
