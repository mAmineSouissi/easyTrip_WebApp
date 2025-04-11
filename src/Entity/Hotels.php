<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity]
class Hotels
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id_hotel = null;

    #[ORM\Column(type: "string", length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: "string", length: 255)]
    private ?string $adresse = null;

    #[ORM\Column(type: "string", length: 255)]
    private ?string $city = null;

    #[ORM\Column(type: "integer")]
    private ?int $rating = null;

    #[ORM\Column(type: "text")]
    private ?string $description = null;

    #[ORM\Column(type: "float")]
    private ?float $price = null;

    #[ORM\Column(type: "string", length: 255)]
    private ?string $type_room = null;

    #[ORM\Column(type: "integer")]
    private ?int $num_room = null;

    #[ORM\Column(type: "string", length: 255)]
    private ?string $image = null;

    #[ORM\Column(type: "integer")]
    private ?int $promotion_id = null;

    #[ORM\Column(type: "integer")]
    private ?int $agency_id = null;

    #[ORM\OneToMany(mappedBy: "hotel", targetEntity: Webinaire::class)]
    private Collection $webinaires;

    public function __construct()
    {
        $this->webinaires = new ArrayCollection();
    }

    public function getIdHotel(): ?int
    {
        return $this->id_hotel;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): self
    {
        $this->adresse = $adresse;
        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;
        return $this;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(int $rating): self
    {
        $this->rating = $rating;
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

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;
        return $this;
    }

    public function getTypeRoom(): ?string
    {
        return $this->type_room;
    }

    public function setTypeRoom(string $type_room): self
    {
        $this->type_room = $type_room;
        return $this;
    }

    public function getNumRoom(): ?int
    {
        return $this->num_room;
    }

    public function setNumRoom(int $num_room): self
    {
        $this->num_room = $num_room;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;
        return $this;
    }

    public function getPromotionId(): ?int
    {
        return $this->promotion_id;
    }

    public function setPromotionId(int $promotion_id): self
    {
        $this->promotion_id = $promotion_id;
        return $this;
    }

    public function getAgencyId(): ?int
    {
        return $this->agency_id;
    }

    public function setAgencyId(int $agency_id): self
    {
        $this->agency_id = $agency_id;
        return $this;
    }

    /**
     * @return Collection<int, Webinaire>
     */
    public function getWebinaires(): Collection
    {
        return $this->webinaires;
    }

    public function addWebinaire(Webinaire $webinaire): self
    {
        if (!$this->webinaires->contains($webinaire)) {
            $this->webinaires->add($webinaire);
            $webinaire->setHotel($this);
        }

        return $this;
    }

    public function removeWebinaire(Webinaire $webinaire): self
    {
        if ($this->webinaires->removeElement($webinaire)) {
            // set the owning side to null (unless already changed)
            if ($webinaire->getHotel() === $this) {
                $webinaire->setHotel(null);
            }
        }

        return $this;
    }
}