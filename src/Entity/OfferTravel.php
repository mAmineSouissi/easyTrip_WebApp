<?php

namespace App\Entity;

use App\Repository\Offer_travelRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: Offer_travelRepository::class)]
#[ORM\Table(name: 'offer_travel')]
class OfferTravel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $departure;

    #[ORM\Column(type: 'string', length: 255)]
    private string $destination;

    #[ORM\Column(type: 'date')]
    private \DateTimeInterface $departure_date;

    #[ORM\Column(type: 'date')]
    private \DateTimeInterface $arrival_date;

    #[ORM\Column(type: 'string', length: 50)]
    private string $hotel_name;

    #[ORM\Column(type: 'string', length: 255)]
    private string $discription;

    #[ORM\Column(type: 'string', length: 255)]
    private string $category;

    #[ORM\Column(type: 'float')]
    private float $price;

    #[ORM\Column(type: 'string', length: 255)]
    private string $image;

    #[ORM\Column(type: 'string', length: 255)]
    private string $flight_name;

    #[ORM\ManyToOne(targetEntity: Agency::class, inversedBy: 'offerTravels')]
    #[ORM\JoinColumn(nullable: false)]
    private Agency $agency;

    #[ORM\ManyToOne(targetEntity: Promotion::class, inversedBy: 'offerTravels')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Promotion $promotion = null;

    // Getters and Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDeparture(): string
    {
        return $this->departure;
    }

    public function setDeparture(string $departure): self
    {
        $this->departure = $departure;
        return $this;
    }

    public function getDestination(): string
    {
        return $this->destination;
    }

    public function setDestination(string $destination): self
    {
        $this->destination = $destination;
        return $this;
    }

    public function getDepartureDate(): \DateTimeInterface
    {
        return $this->departure_date;
    }

    public function setDepartureDate(\DateTimeInterface $departure_date): self
    {
        $this->departure_date = $departure_date;
        return $this;
    }

    public function getArrivalDate(): \DateTimeInterface
    {
        return $this->arrival_date;
    }

    public function setArrivalDate(\DateTimeInterface $arrival_date): self
    {
        $this->arrival_date = $arrival_date;
        return $this;
    }

    public function getHotelName(): string
    {
        return $this->hotel_name;
    }

    public function setHotelName(string $hotel_name): self
    {
        $this->hotel_name = $hotel_name;
        return $this;
    }

    public function getDiscription(): string
    {
        return $this->discription;
    }

    public function setDiscription(string $discription): self
    {
        $this->discription = $discription;
        return $this;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;
        return $this;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;
        return $this;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;
        return $this;
    }

    public function getFlightName(): string
    {
        return $this->flight_name;
    }

    public function setFlightName(string $flight_name): self
    {
        $this->flight_name = $flight_name;
        return $this;
    }

    public function getAgency(): Agency
    {
        return $this->agency;
    }

    public function setAgency(Agency $agency): self
    {
        $this->agency = $agency;
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
}