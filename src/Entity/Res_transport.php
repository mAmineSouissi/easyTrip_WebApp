<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use App\Entity\Cars;
use App\Entity\User;

#[ORM\Entity]
#[ORM\Table(name: 'res_transport')]
class Res_transport
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'reservations')]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id", nullable: false)]
    #[Assert\NotNull(message: "L'utilisateur est requis")]
    private ?User $user = null;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $createdAt;

    #[ORM\ManyToOne(targetEntity: Cars::class, inversedBy: 'reservations')]
    #[ORM\JoinColumn(name: "car_id", referencedColumnName: "id", nullable: false)]
    #[Assert\NotNull(message: "La voiture est requise")]
    #[Assert\Type(type: Cars::class, message: "Type de voiture invalide")]
    private ?Cars $car = null;

    #[ORM\Column(type: "date")]
    #[Assert\NotNull(message: "La date de début est requise")]
    #[Assert\Type("\DateTimeInterface")]
    #[Assert\GreaterThanOrEqual("today", message: "La date de début ne peut pas être dans le passé")]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: "date")]
    #[Assert\NotNull(message: "La date de fin est requise")]
    #[Assert\Type("\DateTimeInterface")]
    #[Assert\Expression(
        "this.getEndDate() > this.getStartDate()",
        message: "La date de fin doit être postérieure à la date de début"
    )]
    #[Assert\GreaterThan(propertyPath: "startDate", message: "La date de fin doit être postérieure à la date de début")]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Le statut est requis")]
    #[Assert\Choice(choices: ["En attente", "Confirmée", "Annulée"], message: "Statut invalide")]
    private string $status = "En attente";

    #[ORM\Column(type: "float")]
    #[Assert\NotBlank(message: "Le prix total est requis")]
    #[Assert\Positive(message: "Le prix total doit être positif")]
    #[Assert\Type(type: "float", message: "Le prix total doit être un nombre")]
    private ?float $totalPrice = null;

    #[ORM\Column(type: "float", nullable: true)]
    private ?float $latitude = null;

    #[ORM\Column(type: "float", nullable: true)]
    private ?float $longitude = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    #[Assert\Callback]
    public function validateDates(ExecutionContextInterface $context, $payload)
    {
        if ($this->startDate && $this->endDate) {
            $interval = $this->startDate->diff($this->endDate);
            $days = $interval->days;

            if ($days <= 0) {
                $context->buildViolation('La réservation doit être d\'au moins une journée')
                    ->atPath('endDate')
                    ->addViolation();
                return;
            }

            if ($days > 30) {
                $context->buildViolation('La réservation ne peut pas dépasser 30 jours')
                    ->atPath('endDate')
                    ->addViolation();
                return;
            }
        }
    }

    #[Assert\Callback]
    public function validateTotalPrice(ExecutionContextInterface $context, $payload)
    {
        if ($this->car && $this->startDate && $this->endDate && $this->totalPrice) {
            $interval = $this->startDate->diff($this->endDate);
            $days = $interval->days + 1;
            $expectedPrice = $this->car->getPricePerDay() * $days;

            if (abs($this->totalPrice - $expectedPrice) > 0.01) {
                $context->buildViolation('Le prix total ne correspond pas au calcul : {{ expected }} €')
                    ->setParameter('{{ expected }}', number_format($expectedPrice, 2))
                    ->atPath('totalPrice')
                    ->addViolation();
            }
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getCar(): ?Cars
    {
        return $this->car;
    }

    public function setCar(?Cars $car): self
    {
        $this->car = $car;
        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getTotalPrice(): ?float
    {
        return $this->totalPrice;
    }

    public function setTotalPrice(float $totalPrice): self
    {
        $this->totalPrice = $totalPrice;
        return $this;
    }

    public function calculateTotalPrice(): float
    {
        if (!$this->car || !$this->startDate || !$this->endDate) {
            return 0.0;
        }

        $interval = $this->startDate->diff($this->endDate);
        $days = $interval->days + 1;
        return $this->car->getPricePerDay() * $days;
    }

    public function isCancellable(): bool
    {
        return $this->status === 'En attente' || $this->status === 'Confirmée';
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): self
    {
        $this->latitude = $latitude;
        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): self
    {
        $this->longitude = $longitude;
        return $this;
    }
}
