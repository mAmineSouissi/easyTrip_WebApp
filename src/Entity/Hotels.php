<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\Collection;
use App\Entity\Webinaire;

#[ORM\Entity]
class Hotels
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id_hotel;

    
    #[ORM\Column(type: "string", length: 255)]
    private string $name;

    #[ORM\Column(type: "string", length: 255)]
    private string $adresse;

    #[ORM\Column(type: "string", length: 255)]
    private string $city;

    #[ORM\Column(type: "integer")]
    private int $rating;

    #[ORM\Column(type: "text")]
    private string $description;

    #[ORM\Column(type: "float")]
    private float $price;

    #[ORM\Column(type: "string", length: 255)]
    private string $type_room;

    #[ORM\Column(type: "integer")]
    private int $num_room;

    #[ORM\Column(type: "string", length: 255)]
    private string $image;

    #[ORM\Column(type: "integer")]
    private int $promotion_id;

    #[ORM\Column(type: "integer")]
    private int $agency_id;

    public function getId_hotel()
    {
        return $this->id_hotel;
    }

    public function setId_hotel($value)
    {
        $this->id_hotel = $value;
    }

    public function getUser_id()
    {
        return $this->user_id;
    }

    public function setUser_id($value)
    {
        $this->user_id = $value;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($value)
    {
        $this->name = $value;
    }

    public function getAdresse()
    {
        return $this->adresse;
    }

    public function setAdresse($value)
    {
        $this->adresse = $value;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function setCity($value)
    {
        $this->city = $value;
    }

    public function getRating()
    {
        return $this->rating;
    }

    public function setRating($value)
    {
        $this->rating = $value;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($value)
    {
        $this->description = $value;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function setPrice($value)
    {
        $this->price = $value;
    }

    public function getType_room()
    {
        return $this->type_room;
    }

    public function setType_room($value)
    {
        $this->type_room = $value;
    }

    public function getNum_room()
    {
        return $this->num_room;
    }

    public function setNum_room($value)
    {
        $this->num_room = $value;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setImage($value)
    {
        $this->image = $value;
    }

    public function getPromotion_id()
    {
        return $this->promotion_id;
    }

    public function setPromotion_id($value)
    {
        $this->promotion_id = $value;
    }

    public function getAgency_id()
    {
        return $this->agency_id;
    }

    public function setAgency_id($value)
    {
        $this->agency_id = $value;
    }

    #[ORM\OneToMany(mappedBy: "hotel_id", targetEntity: Webinaire::class)]
    private Collection $webinaires;

        public function getWebinaires(): Collection
        {
            return $this->webinaires;
        }
    
        public function addWebinaire(Webinaire $webinaire): self
        {
            if (!$this->webinaires->contains($webinaire)) {
                $this->webinaires[] = $webinaire;
                $webinaire->setHotel_id($this);
            }
    
            return $this;
        }
    
        public function removeWebinaire(Webinaire $webinaire): self
        {
            if ($this->webinaires->removeElement($webinaire)) {
                // set the owning side to null (unless already changed)
                if ($webinaire->getHotel_id() === $this) {
                    $webinaire->setHotel_id(null);
                }
            }
    
            return $this;
        }
}
