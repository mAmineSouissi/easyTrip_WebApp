<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'promotion')]
class Promotion
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: "Le titre est obligatoire")]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "Le titre doit faire au moins {{ limit }} caractères",
        maxMessage: "Le titre ne peut pas dépasser {{ limit }} caractères"
    )]
    private string $title;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: "La description est obligatoire")]
    #[Assert\Length(
        min: 10,
        minMessage: "La description doit faire au moins {{ limit }} caractères"
    )]
    private string $description;

    #[ORM\Column(type: 'float')]
    #[Assert\NotBlank(message: "Le pourcentage de réduction est obligatoire")]
    #[Assert\Range(
        min: 1,
        max: 100,
        notInRangeMessage: "Le pourcentage doit être entre {{ min }}% et {{ max }}%"
    )]
    private float $discount_percentage;

    #[ORM\Column(type: 'date')]
    #[Assert\NotBlank(message: "La date de validité est obligatoire")]
    #[Assert\GreaterThan(
        "today",
        message: "La date doit être postérieure à aujourd'hui"
    )]
    private \DateTimeInterface $valid_until;

    #[ORM\OneToMany(mappedBy: 'promotion', targetEntity: Hotels::class)]
    private Collection $hotels;

    public function __construct()
    {
        $this->hotels = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getDiscountPercentage(): float
    {
        return $this->discount_percentage;
    }

    public function setDiscountPercentage(float $discount_percentage): self
    {
        $this->discount_percentage = $discount_percentage;
        return $this;
    }

    public function getValidUntil(): \DateTimeInterface
    {
        return $this->valid_until;
    }

    public function setValidUntil(\DateTimeInterface $valid_until): self
    {
        $this->valid_until = $valid_until;
        return $this;
    }

    /**
     * @return Collection<int, Hotels>
     */
    public function getHotels(): Collection
    {
        return $this->hotels;
    }

    public function addHotel(Hotels $hotel): self
    {
        if (!$this->hotels->contains($hotel)) {
            $this->hotels[] = $hotel;
            $hotel->setPromotion($this);
        }
        return $this;
    }

    public function removeHotel(Hotels $hotel): self
    {
        if ($this->hotels->removeElement($hotel)) {
            if ($hotel->getPromotion() === $this) {
                $hotel->setPromotion(null);
            }
        }
        return $this;
    }
}