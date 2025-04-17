<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\User;

#[ORM\Entity]
class Tickets
{
    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id_ticket;

    #[ORM\Column(type: "integer")]
    private int $flight_number;

    #[ORM\Column(type: "string", length: 255)]
    private string $airline;

    #[ORM\Column(type: "string", length: 255)]
    private string $departure_city;

    #[ORM\Column(type: "string", length: 255)]
    private string $arrival_city;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $departure_date;

    #[ORM\Column(type: "string")]
    private string $departure_time;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $arrival_date;

    #[ORM\Column(type: "string")]
    private string $arrival_time;

    #[ORM\Column(type: "string", length: 50)]
    private string $ticket_class;

    #[ORM\Column(type: "float")]
    private float $price;

    #[ORM\Column(type: "string", length: 50)]
    private string $ticket_type;

    #[ORM\Column(type: "string", length: 255)]
    private string $image_airline;

    #[ORM\Column(type: "string", length: 1000)]
    private string $city_image;

    #[ORM\Column(type: "integer")]
    private int $agency_id;

    #[ORM\Column(type: "integer")]
    private int $promotion_id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id", nullable: true, onDelete: "SET NULL")]
    private ?User $user = null;

    // 🔁 Getters/Setters

    public function getId_ticket(): int { return $this->id_ticket; }
    public function setId_ticket(int $value): void { $this->id_ticket = $value; }

    public function getFlight_number(): int { return $this->flight_number; }
    public function setFlight_number(int $value): void { $this->flight_number = $value; }

    public function getAirline(): string { return $this->airline; }
    public function setAirline(string $value): void { $this->airline = $value; }

    public function getDeparture_city(): string { return $this->departure_city; }
    public function setDeparture_city(string $value): void { $this->departure_city = $value; }

    public function getArrival_city(): string { return $this->arrival_city; }
    public function setArrival_city(string $value): void { $this->arrival_city = $value; }

    public function getDeparture_date(): \DateTimeInterface { return $this->departure_date; }
    public function setDeparture_date(\DateTimeInterface $value): void { $this->departure_date = $value; }

    public function getDeparture_time(): string { return $this->departure_time; }
    public function setDeparture_time(string $value): void { $this->departure_time = $value; }

    public function getArrival_date(): \DateTimeInterface { return $this->arrival_date; }
    public function setArrival_date(\DateTimeInterface $value): void { $this->arrival_date = $value; }

    public function getArrival_time(): string { return $this->arrival_time; }
    public function setArrival_time(string $value): void { $this->arrival_time = $value; }

    public function getTicket_class(): string { return $this->ticket_class; }
    public function setTicket_class(string $value): void { $this->ticket_class = $value; }

    public function getPrice(): float { return $this->price; }
    public function setPrice(float $value): void { $this->price = $value; }

    public function getTicket_type(): string { return $this->ticket_type; }
    public function setTicket_type(string $value): void { $this->ticket_type = $value; }

    public function getImage_airline(): string { return $this->image_airline; }
    public function setImage_airline(string $value): void { $this->image_airline = $value; }

    public function getCity_image(): string { return $this->city_image; }
    public function setCity_image(string $value): void { $this->city_image = $value; }

    public function getAgency_id(): int { return $this->agency_id; }
    public function setAgency_id(int $value): void { $this->agency_id = $value; }

    public function getPromotion_id(): int { return $this->promotion_id; }
    public function setPromotion_id(int $value): void { $this->promotion_id = $value; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): void { $this->user = $user; }
}
