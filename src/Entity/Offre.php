<?php

namespace App\Entity;

use App\Repository\OffreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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


    #[ORM\OneToMany(mappedBy: 'offre', targetEntity: Notification::class)]
    private Collection $notifications;

    #[ORM\Column]
    private ?bool $isDeleted = false;

    public function __construct()
    {
        $this->notifications = new ArrayCollection();
    }


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



    /**
     * @return Collection<int, Notification>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): self
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->setOffre($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): self
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getOffre() === $this) {
                $notification->setOffre(null);
            }
        }

        return $this;
    }

    public function isIsDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }
}
