<?php

namespace App\Entity;

use App\Repository\Offer_travelRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;

#[ORM\Entity(repositoryClass: Offer_TravelRepository::class)]
class OfferTravel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le champ Départ est obligatoire")]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: "Le départ doit contenir au moins {{ limit }} caractères",
        maxMessage: "Le départ ne peut pas dépasser {{ limit }} caractères"
    )]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z\s\-]+$/',
        message: "Le départ ne doit contenir que des lettres, espaces et tirets"
    )]
    private ?string $departure = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le champ Destination est obligatoire")]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: "La destination doit contenir au moins {{ limit }} caractères",
        maxMessage: "La destination ne peut pas dépasser {{ limit }} caractères"
    )]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z\s\-]+$/',
        message: "La destination ne doit contenir que des lettres, espaces et tirets"
    )]
    private ?string $destination = null;

    #[ORM\Column(type: 'date')]
    #[Assert\NotBlank(message: "La date de départ est obligatoire")]
    #[Assert\GreaterThanOrEqual(
        value: "today",
        message: "La date de départ doit être aujourd'hui ou dans le futur"
    )]
    private ?\DateTimeInterface $departure_date = null;

    #[ORM\Column(type: 'date')]
    #[Assert\NotBlank(message: "La date d'arrivée est obligatoire")]
    #[Assert\GreaterThan(
        propertyPath: "departure_date",
        message: "La date d'arrivée doit être après la date de départ"
    )]
    private ?\DateTimeInterface $arrival_date = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom de l'hôtel est obligatoire")]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: "Le nom de l'hôtel doit contenir au moins {{ limit }} caractères",
        maxMessage: "Le nom de l'hôtel ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $hotel_name = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: "La description est obligatoire")]
    #[Assert\Length(
        min: 10,
        max: 2000,
        minMessage: "La description doit contenir au moins {{ limit }} caractères",
        maxMessage: "La description ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $discription = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La catégorie est obligatoire")]
    #[Assert\Choice(
        choices: ["Sportive", "Romantique", "Religieuse", "Touristique"],
        message: "Choisissez une catégorie valide"
    )]
    private ?string $category = null;

    #[ORM\Column(type: 'float')]
    #[Assert\NotBlank(message: "Le prix est obligatoire")]
    #[Assert\GreaterThan(
        value: 0,
        message: "Le prix doit être supérieur à 0"
    )]
    #[Assert\LessThan(
        value: 100000,
        message: "Le prix doit être inférieur à 100000"
    )]
    private ?float $price = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[Assert\NotBlank(message: "L'image est obligatoire", groups: ['create'])]
    #[Assert\Image([
        'maxSize' => '2M',
        'mimeTypes' => ['image/jpeg', 'image/png'],
        'mimeTypesMessage' => 'Veuillez uploader une image valide (JPEG ou PNG)',
        'groups' => ['create', 'update']
    ])]
    private ?File $imageFile = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom du vol est obligatoire")]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: "Le nom du vol doit contenir au moins {{ limit }} caractères",
        maxMessage: "Le nom du vol ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $flight_name = null;

    #[ORM\ManyToOne(targetEntity: Agency::class)]
    #[Assert\NotBlank(message: "L'agence est obligatoire")]
    private ?Agency $agency = null;

    #[ORM\ManyToOne(targetEntity: Promotion::class)]
    private ?Promotion $promotion = null;
    

    // Getters et setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDeparture(): ?string
    {
        return $this->departure;
    }

    public function setDeparture(string $departure): self
    {
        $this->departure = $departure;
        return $this;
    }

    public function getDestination(): ?string
    {
        return $this->destination;
    }

    public function setDestination(string $destination): self
    {
        $this->destination = $destination;
        return $this;
    }

    public function getDepartureDate(): ?\DateTimeInterface
    {
        return $this->departure_date;
    }

    public function setDepartureDate(\DateTimeInterface $departure_date): self
    {
        $this->departure_date = $departure_date;
        return $this;
    }

    public function getArrivalDate(): ?\DateTimeInterface
    {
        return $this->arrival_date;
    }

    public function setArrivalDate(\DateTimeInterface $arrival_date): self
    {
        $this->arrival_date = $arrival_date;
        return $this;
    }

    public function getHotelName(): ?string
    {
        return $this->hotel_name;
    }

    public function setHotelName(string $hotel_name): self
    {
        $this->hotel_name = $hotel_name;
        return $this;
    }

    public function getDiscription(): ?string
    {
        return $this->discription;
    }

    public function setDiscription(string $discription): self
    {
        $this->discription = $discription;
        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;
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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;
        return $this;
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageFile(?File $imageFile = null): self
    {
        $this->imageFile = $imageFile;
        return $this;
    }

    public function getFlightName(): ?string
    {
        return $this->flight_name;
    }

    public function setFlightName(string $flight_name): self
    {
        $this->flight_name = $flight_name;
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