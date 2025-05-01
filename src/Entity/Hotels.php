<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class Hotels
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id_hotel = null;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Le nom de l'hôtel est requis")]
    #[Assert\Length(max: 255, maxMessage: "Le nom ne peut pas dépasser {{ limit }} caractères")]
    private ?string $name = null;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "L'adresse est requise")]
    #[Assert\Length(max: 255, maxMessage: "L'adresse ne peut pas dépasser {{ limit }} caractères")]
    private ?string $adresse = null;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "La ville est requise")]
    #[Assert\Length(max: 255, maxMessage: "La ville ne peut pas dépasser {{ limit }} caractères")]
    private ?string $city = null;

    #[ORM\Column(type: "integer")]
    #[Assert\NotBlank(message: "La note est requise")]
    #[Assert\Range(min: 1, max: 5, notInRangeMessage: "La note doit être entre {{ min }} et {{ max }}")]
    private ?int $rating = null;

    #[ORM\Column(type: "text")]
    #[Assert\NotBlank(message: "La description est requise")]
    private ?string $description = null;

    #[ORM\Column(type: "float")]
    #[Assert\NotBlank(message: "Le prix est requis")]
    #[Assert\GreaterThan(value: 0, message: "Le prix doit être supérieur à 0")]
    #[Assert\Type(type: "numeric", message: "Le prix doit être une valeur numérique")]
    private ?float $price = null;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Le type de chambre est requis")]
    #[Assert\Length(max: 255, maxMessage: "Le type de chambre ne peut pas dépasser {{ limit }} caractères")]
    private ?string $type_room = null;

    #[ORM\Column(type: "integer")]
    #[Assert\NotBlank(message: "Le nombre de chambres est requis")]
    #[Assert\GreaterThan(value: 0, message: "Le nombre de chambres doit être supérieur à 0")]
    private ?int $num_room = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\ManyToOne(targetEntity: Promotion::class)]
    #[ORM\JoinColumn(name: "promotion_id", referencedColumnName: "id", nullable: true)]
    private ?Promotion $promotion = null;

    #[ORM\ManyToOne(targetEntity: Agency::class)]
    #[ORM\JoinColumn(name: "agency_id", referencedColumnName: "id", nullable: false)]
    private ?Agency $agency = null;

    public function getIdHotel(): ?int
    {
        return $this->id_hotel;
    }

    public function getId(): ?int
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

    public function getOriginalPrice(): ?float
    {
        return $this->price;
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

    public function setImage(?string $image): self
    {
        $this->image = $image;
        return $this;
    }

    public function getPromotion(): ?Promotion
    {
        return $this->promotion;
    }

    public function setPromotion(?Promotion $promotion): self
    {
        $this->promotion = $promotion;
        return $this;
    }

    public function getAgency(): ?Agency
    {
        return $this->agency;
    }

    public function setAgency(?Agency $agency): self
    {
        $this->agency = $agency;
        return $this;
    }
}