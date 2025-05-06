<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\User;
use Doctrine\Common\Collections\Collection;
use App\Entity\Panier;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class Reservation
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id_reservation;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "reservations")]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $user_id;

    #[ORM\Column(type: "integer")]
    private int $travel_id;

    #[ORM\Column(type: "string", length: 255)]
    private string $status;

    #[ORM\Column(type: "date", name: 'orderDate')]
    private \DateTimeInterface $orderDate;

    #[ORM\Column(type: "integer")]
    private int $ticket_id;

    #[ORM\Column(type: "integer")]
    private int $hotel_id;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Le nom est obligatoire !")]
    private string $nom;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Le prénom est obligatoire !")]
    private string $prenom;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Le numéro de téléphone  est obligatoire !")]
    #[Assert\Regex(
        pattern: '/^\d{8}$/',
        message: "Le téléphone doit être un nombre valide."
    )]
    private string $phone;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "L'E-mail est obligatoire !")]
    #[Assert\Email(message: "L'adresse e-mail '{{ value }}' n'est pas valide !")]
    private string $email;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Le nombre de places est obligatoire !")]
    #[Assert\Regex(
    pattern: '/^\d+$/',
    message: "Le nombre de places doit être valide !"
     )]
    private string $places;

    public function getId_reservation()
    {
        return $this->id_reservation;
    }

    public function setId_reservation($value)
    {
        $this->id_reservation = $value;
    }

    public function getUser_id()
    {
        return $this->user_id;
    }

    public function setUser_id($value)
    {
        $this->user_id = $value;
    }

    public function getTravel_id()
    {
        return $this->travel_id;
    }

    public function setTravel_id($value)
    {
        $this->travel_id = $value;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($value)
    {
        $this->status = $value;
    }

    public function getOrderDate()
    {
        return $this->orderDate;
    }

    public function setOrderDate($value)
    {
        $this->orderDate = $value;
    }

    public function getTicket_id()
    {
        return $this->ticket_id;
    }

    public function setTicket_id($value)
    {
        $this->ticket_id = $value;
    }

    public function getHotel_id()
    {
        return $this->hotel_id;
    }

    public function setHotel_id($value)
    {
        $this->hotel_id = $value;
    }

    public function getNom()
    {
        return $this->nom;
    }

    public function setNom($value)
    {
        $this->nom = $value;
    }

    public function getPrenom()
    {
        return $this->prenom;
    }

    public function setPrenom($value)
    {
        $this->prenom = $value;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function setPhone($value)
    {
        $this->phone = $value;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($value)
    {
        $this->email = $value;
    }

    public function getPlaces()
    {
        return $this->places;
    }

    public function setPlaces($value)
    {
        $this->places = $value;
    }

    #[ORM\OneToMany(mappedBy: "reservation_id", targetEntity: Panier::class)]
    private Collection $paniers;

        public function getPaniers(): Collection
        {
            return $this->paniers;
        }
    
        public function addPanier(Panier $panier): self
        {
            if (!$this->paniers->contains($panier)) {
                $this->paniers[] = $panier;
                $panier->setReservation_id($this);
            }
    
            return $this;
        }
    
        public function removePanier(Panier $panier): self
        {
            if ($this->paniers->removeElement($panier)) {
                // set the owning side to null (unless already changed)
                if ($panier->getReservation_id() === $this) {
                    $panier->setReservation_id(null);
                }
            }
    
            return $this;
        }
}