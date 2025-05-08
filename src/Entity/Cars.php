<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'cars')]
class Cars
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Le modèle est requis")]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: "Le modèle doit contenir au moins {{ limit }} caractères",
        maxMessage: "Le modèle ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $model = null;

    #[ORM\Column(type: "integer")]
    #[Assert\NotBlank(message: "Le nombre de places est requis")]
    #[Assert\Positive(message: "Le nombre de places doit être positif")]
    #[Assert\Range(
        min: 2,
        max: 8,
        notInRangeMessage: "Le nombre de places doit être entre {{ min }} et {{ max }}"
    )]
    private ?int $seats = null;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "La localisation est requise")]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: "La localisation doit contenir au moins {{ limit }} caractères",
        maxMessage: "La localisation ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $location = null;

    #[ORM\Column(type: "float", nullable: true)]
    private ?float $latitude = null;

    #[ORM\Column(type: "float", nullable: true)]
    private ?float $longitude = null;

    #[ORM\Column(type: "float")]
    #[Assert\NotNull(message: "Le prix par jour est requis")]
    #[Assert\Positive(message: "Le prix par jour doit être positif")]
    #[Assert\Range(
        min: 10,
        max: 1000,
        notInRangeMessage: "Le prix par jour doit être entre {{ min }}€ et {{ max }}€"
    )]
    private ?float $price_per_day = null;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "L'image est requise")]
    #[Assert\Url(message: "L'URL de l'image n'est pas valide")]
    private ?string $image = null;

    #[ORM\OneToMany(mappedBy: "car", targetEntity: Res_transport::class)]
    private Collection $reservations;

    public function __construct()
    {
        $this->reservations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(?string $value): static
    {
        $this->model = $value;
        return $this;
    }

    public function getSeats(): ?int
    {
        return $this->seats;
    }

    public function setSeats(int $seats): static
    {
        $this->seats = $seats;
        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): static
    {
        $this->location = $location;
        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): static
    {
        $this->latitude = $latitude;
        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): static
    {
        $this->longitude = $longitude;
        return $this;
    }

    public function getPricePerDay(): ?float
    {
        return $this->price_per_day;
    }

    public function setPricePerDay(float $price_per_day): static
    {
        $this->price_per_day = $price_per_day;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $value): static
    {
        $this->image = $value;
        return $this;
    }

    /**
     * @return Collection|Res_transport[]
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Res_transport $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations[] = $reservation;
            $reservation->setCar($this);
        }

        return $this;
    }

    public function removeReservation(Res_transport $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getCar() === $this) {
                $reservation->setCar(null);
            }
        }

        return $this;
    }

    public function isAvailableForDates(\DateTimeInterface $startDate, \DateTimeInterface $endDate, ?Res_transport $excludeReservation = null): bool
    {
        foreach ($this->reservations as $reservation) {
            // Ignorer la réservation en cours d'édition
            if ($excludeReservation && $reservation->getId() === $excludeReservation->getId()) {
                continue;
            }

            // Vérifier si la réservation est confirmée et si les dates se chevauchent
            if ($reservation->getStatus() === 'Confirmée' &&
                $startDate <= $reservation->getEndDate() &&
                $endDate >= $reservation->getStartDate()
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Obtenir toutes les périodes réservées
     * @return array Array of arrays containing start and end dates
     */
    public function getReservedPeriods(): array
    {
        $periods = [];
        foreach ($this->reservations as $reservation) {
            if ($reservation->getStatus() === 'Confirmée') {
                $periods[] = [
                    'start' => $reservation->getStartDate(),
                    'end' => $reservation->getEndDate()
                ];
            }
        }
        return $periods;
    }
}
