<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\User;
use Doctrine\Common\Collections\Collection;
use App\Entity\Panier;

#[ORM\Entity]
class Reservation
{

    #[ORM\Id]
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
    private string $nom;

    #[ORM\Column(type: "string", length: 255)]
    private string $prenom;

    #[ORM\Column(type: "string", length: 255)]
    private string $phone;

    #[ORM\Column(type: "string", length: 255)]
    private string $email;

    #[ORM\Column(type: "string", length: 255)]
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
