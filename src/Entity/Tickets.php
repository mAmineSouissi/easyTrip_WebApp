<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "App\Repository\TicketsRepository")]
class Tickets
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $idTicket = null;

    #[ORM\Column(type: 'integer')]
    private int $flightNumber;

    #[ORM\Column(type: 'string', length: 255)]
    private string $airline;

    #[ORM\Column(type: 'string', length: 255)]
    private string $departureCity;

    #[ORM\Column(type: 'string', length: 255)]
    private string $arrivalCity;

    #[ORM\Column(type: 'date')]
    private \DateTimeInterface $departureDate;

    #[ORM\Column(type: 'string', length: 8)]
    private string $departureTime;

    #[ORM\Column(type: 'date')]
    private \DateTimeInterface $arrivalDate;

    #[ORM\Column(type: 'string', length: 8)]
    private string $arrivalTime;

    #[ORM\Column(type: 'string', length: 50)]
    private string $ticketClass;

    #[ORM\Column(type: 'float')]
    private float $price;

    #[ORM\Column(type: 'string', length: 50)]
    private string $ticketType;

    #[ORM\Column(type: 'string', length: 255)]
    private string $imageAirline;

    #[ORM\Column(type: 'string', length: 1000)]
    private string $cityImage;

    #[ORM\Column(type: 'integer')]
    private int $agencyId;

    #[ORM\Column(type: 'integer')]
    private int $promotionId;

    // Getters and Setters
    public function getIdTicket(): ?int
    {
        return $this->idTicket;
    }

    public function getFlightNumber(): int
    {
        return $this->flightNumber;
    }

    public function setFlightNumber(int $flightNumber): self
    {
        $this->flightNumber = $flightNumber;
        return $this;
    }

    public function getAirline(): string
    {
        return $this->airline;
    }

    public function setAirline(string $airline): self
    {
        $this->airline = $airline;
        return $this;
    }

    public function getDepartureCity(): string
    {
        return $this->departureCity;
    }

    public function setDepartureCity(string $departureCity): self
    {
        $this->departureCity = $departureCity;
        return $this;
    }

    public function getArrivalCity(): string
    {
        return $this->arrivalCity;
    }

    public function setArrivalCity(string $arrivalCity): self
    {
        $this->arrivalCity = $arrivalCity;
        return $this;
    }

    public function getDepartureDate(): \DateTimeInterface
    {
        return $this->departureDate;
    }

    public function setDepartureDate(\DateTimeInterface $departureDate): self
    {
        $this->departureDate = $departureDate;
        return $this;
    }

    public function getDepartureTime(): string
    {
        return $this->departureTime;
    }

    public function setDepartureTime(string $departureTime): self
    {
        $this->departureTime = $departureTime;
        return $this;
    }

    public function getArrivalDate(): \DateTimeInterface
    {
        return $this->arrivalDate;
    }

    public function setArrivalDate(\DateTimeInterface $arrivalDate): self
    {
        $this->arrivalDate = $arrivalDate;
        return $this;
    }

    public function getArrivalTime(): string
    {
        return $this->arrivalTime;
    }

    public function setArrivalTime(string $arrivalTime): self
    {
        $this->arrivalTime = $arrivalTime;
        return $this;
    }

    public function getTicketClass(): string
    {
        return $this->ticketClass;
    }

    public function setTicketClass(string $ticketClass): self
    {
        $this->ticketClass = $ticketClass;
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

    public function getTicketType(): string
    {
        return $this->ticketType;
    }

    public function setTicketType(string $ticketType): self
    {
        $this->ticketType = $ticketType;
        return $this;
    }

    public function getImageAirline(): string
    {
        return $this->imageAirline;
    }

    public function setImageAirline(string $imageAirline): self
    {
        $this->imageAirline = $imageAirline;
        return $this;
    }

    public function getCityImage(): string
    {
        return $this->cityImage;
    }

    public function setCityImage(string $cityImage): self
    {
        $this->cityImage = $cityImage;
        return $this;
    }

    public function getAgencyId(): int
    {
        return $this->agencyId;
    }

    public function setAgencyId(int $agencyId): self
    {
        $this->agencyId = $agencyId;
        return $this;
    }

    public function getPromotionId(): int
    {
        return $this->promotionId;
    }

    public function setPromotionId(int $promotionId): self
    {
        $this->promotionId = $promotionId;
        return $this;
    }
}