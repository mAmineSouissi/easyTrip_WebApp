<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: "App\Repository\TicketsRepository")]
class Tickets
{
    #[ORM\Id]
    #[ORM\GeneratedValue]  // <-- Cette ligne est cruciale
    #[ORM\Column(type: 'integer')]
    private ?int $idTicket = null;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotBlank(message: "Le numéro de vol est requis.")]
    #[Assert\Positive(message: "Le numéro de vol doit être un nombre positif.")]
    private int $flightNumber;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: "La compagnie aérienne est requise.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "La compagnie aérienne ne peut pas dépasser {{ limit }} caractères."
    )]
    private string $airline;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: "La ville de départ est requise.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "La ville de départ ne peut pas dépasser {{ limit }} caractères."
    )]
    private string $departureCity;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: "La ville d'arrivée est requise.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "La ville d'arrivée ne peut pas dépasser {{ limit }} caractères."
    )]
    private string $arrivalCity;

    #[ORM\Column(type: 'date', nullable: true)]
    #[Assert\NotBlank(message: "La date de départ est requise.")]
    #[Assert\GreaterThanOrEqual(
        "today",
        message: "La date de départ doit être aujourd'hui ou dans le futur."
    )]
    private ?\DateTimeInterface $departureDate = null;

    #[ORM\Column(type: 'string', length: 8)]
    #[Assert\NotBlank(message: "L'heure de départ est requise.")]
    #[Assert\Regex(
        pattern: "/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/",
        message: "L'heure de départ doit être au format HH:MM (ex. 14:30)."
    )]
    private string $departureTime;

    #[ORM\Column(type: 'date', nullable: true)]
    #[Assert\NotBlank(message: "La date d'arrivée est requise.")]
    private ?\DateTimeInterface $arrivalDate = null;

    #[ORM\Column(type: 'string', length: 8)]
    #[Assert\NotBlank(message: "L'heure d'arrivée est requise.")]
    #[Assert\Regex(
        pattern: "/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/",
        message: "L'heure d'arrivée doit être au format HH:MM (ex. 16:45)."
    )]
    private string $arrivalTime;

    #[ORM\Column(type: 'string', length: 50)]
    #[Assert\NotBlank(message: "La classe du ticket est requise.")]
    #[Assert\Choice(
        choices: ["Economy", "Business", "First"],
        message: "La classe doit être Economy, Business ou First."
    )]
    private string $ticketClass;

    #[ORM\Column(type: 'float')]
    #[Assert\NotBlank(message: "Le prix est requis.")]
    #[Assert\Positive(message: "Le prix doit être positif.")]
    private float $price;

    #[ORM\Column(type: 'string', length: 50)]
    #[Assert\NotBlank(message: "Le type de ticket est requis.")]
    #[Assert\Choice(
        choices: ["One-way", "Round-trip"],
        message: "Le type de ticket doit être One-way ou Round-trip."
    )]
    private string $ticketType;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $imageAirline = null;

    #[ORM\Column(type: 'string', length: 1000, nullable: true)]
    private ?string $cityImage = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $agencyId = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $promotionId = null;

    #[Assert\Callback]
    public function validateDateTime(ExecutionContextInterface $context): void
    {
        if ($this->departureCity === $this->arrivalCity && $this->departureCity !== null && $this->arrivalCity !== null) {
            $context->buildViolation("La ville de départ et la ville d'arrivée ne peuvent pas être identiques.")
                ->atPath('arrivalCity')
                ->addViolation();
        }

        if ($this->departureDate && $this->arrivalDate && $this->departureTime && $this->arrivalTime) {
            $departureDateTime = \DateTime::createFromFormat(
                'Y-m-d H:i',
                $this->departureDate->format('Y-m-d') . ' ' . $this->departureTime
            );
            $arrivalDateTime = \DateTime::createFromFormat(
                'Y-m-d H:i',
                $this->arrivalDate->format('Y-m-d') . ' ' . $this->arrivalTime
            );

            if ($departureDateTime === false || $arrivalDateTime === false) {
                $context->buildViolation("Les dates et heures fournies sont invalides.")
                    ->atPath('arrivalDate')
                    ->addViolation();
                return;
            }

            if ($arrivalDateTime <= $departureDateTime) {
                $context->buildViolation("La date et l'heure d'arrivée doivent être postérieures à la date et l'heure de départ.")
                    ->atPath('arrivalDate')
                    ->addViolation();
            }
        }
    }

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

    public function getDepartureDate(): ?\DateTimeInterface
    {
        return $this->departureDate;
    }

    public function setDepartureDate(?\DateTimeInterface $departureDate): self
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

    public function getArrivalDate(): ?\DateTimeInterface
    {
        return $this->arrivalDate;
    }

    public function setArrivalDate(?\DateTimeInterface $arrivalDate): self
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

    public function getImageAirline(): ?string
    {
        return $this->imageAirline;
    }

    public function setImageAirline(?string $imageAirline): self
    {
        $this->imageAirline = $imageAirline;
        return $this;
    }

    public function getCityImage(): ?string
    {
        return $this->cityImage;
    }

    public function setCityImage(?string $cityImage): self
    {
        $this->cityImage = $cityImage;
        return $this;
    }

    public function getAgencyId(): ?int
    {
        return $this->agencyId;
    }

    public function setAgencyId(?int $agencyId): self
    {
        $this->agencyId = $agencyId;
        return $this;
    }

    public function getPromotionId(): ?int
    {
        return $this->promotionId;
    }

    public function setPromotionId(?int $promotionId): self
    {
        $this->promotionId = $promotionId;
        return $this;
    }
}