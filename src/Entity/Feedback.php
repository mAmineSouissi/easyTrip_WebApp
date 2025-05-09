<?php

namespace App\Entity;

use App\Entity\Hotels;
use App\Entity\Tickets;
use App\Entity\OfferTravel;
use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: "feedback")]
class Feedback
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "integer")]
    #[Assert\NotNull(message: "La note est requise.")]
    #[Assert\Range(min: 1, max: 5, notInRangeMessage: "La note doit être entre {{ min }} et {{ max }}.")]
    private int $rating;

    #[ORM\Column(type: "string", length: 50)]
    #[Assert\NotBlank(message: "Le message est obligatoire.")]
    #[Assert\Length(max: 50, maxMessage: "Le message ne peut pas dépasser {{ limit }} caractères.")]
    private string $message;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $date;

    #[ORM\ManyToOne(targetEntity: Tickets::class)]
    #[ORM\JoinColumn(name: "ticket_id", referencedColumnName: "id_ticket", nullable: true, onDelete: "SET NULL")]
    private ?Tickets $ticket = null;

    #[ORM\ManyToOne(targetEntity: OfferTravel::class)]
    #[ORM\JoinColumn(name: "travel_id", referencedColumnName: "id", nullable: true, onDelete: "SET NULL")]
    private ?OfferTravel $travel = null;

    #[ORM\ManyToOne(targetEntity: Hotels::class)]
    #[ORM\JoinColumn(name: "hotel_id", referencedColumnName: "id_hotel", nullable: true, onDelete: "SET NULL")]
    private ?Hotels $hotel = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'feedbacks')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    public function __construct()
    {
        $this->date = new \DateTimeImmutable();
    }

    // Getters & Setters

    public function getId(): ?int { return $this->id; }

    public function getRating(): ?int { return $this->rating; }
    public function setRating(int $rating): static { $this->rating = $rating; return $this; }

    public function getMessage(): ?string { return $this->message; }
    public function setMessage(string $message): static { $this->message = $message; return $this; }

    public function getDate(): ?\DateTimeInterface { return $this->date; }
    public function setDate(\DateTimeInterface $date): static { $this->date = $date; return $this; }

    public function getHotel(): ?Hotels { return $this->hotel; }
    public function setHotel(?Hotels $hotel): static { $this->hotel = $hotel; return $this; }

    public function getTicket(): ?Tickets { return $this->ticket; }
    public function setTicket(?Tickets $ticket): static { $this->ticket = $ticket; return $this; }

    public function getTravel(): ?OfferTravel { return $this->travel; }
    public function setTravel(?OfferTravel $travel): static { $this->travel = $travel; return $this; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): static { $this->user = $user; return $this; }
}
