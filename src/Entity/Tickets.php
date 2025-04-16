<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


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

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $user_id = null;

    public function getId_ticket()
    {
        return $this->id_ticket;
    }

    public function setId_ticket($value)
    {
        $this->id_ticket = $value;
    }

    public function getUser_id()
    {
        return $this->user_id;
    }

    public function setUser_id($value)
    {
        $this->user_id = $value;
    }

    public function getFlight_number()
    {
        return $this->flight_number;
    }

    public function setFlight_number($value)
    {
        $this->flight_number = $value;
    }

    public function getAirline()
    {
        return $this->airline;
    }

    public function setAirline($value)
    {
        $this->airline = $value;
    }

    public function getDeparture_city()
    {
        return $this->departure_city;
    }

    public function setDeparture_city($value)
    {
        $this->departure_city = $value;
    }

    public function getArrival_city()
    {
        return $this->arrival_city;
    }

    public function setArrival_city($value)
    {
        $this->arrival_city = $value;
    }

    public function getDeparture_date()
    {
        return $this->departure_date;
    }

    public function setDeparture_date($value)
    {
        $this->departure_date = $value;
    }

    public function getDeparture_time()
    {
        return $this->departure_time;
    }

    public function setDeparture_time($value)
    {
        $this->departure_time = $value;
    }

    public function getArrival_date()
    {
        return $this->arrival_date;
    }

    public function setArrival_date($value)
    {
        $this->arrival_date = $value;
    }

    public function getArrival_time()
    {
        return $this->arrival_time;
    }

    public function setArrival_time($value)
    {
        $this->arrival_time = $value;
    }

    public function getTicket_class()
    {
        return $this->ticket_class;
    }

    public function setTicket_class($value)
    {
        $this->ticket_class = $value;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function setPrice($value)
    {
        $this->price = $value;
    }

    public function getTicket_type()
    {
        return $this->ticket_type;
    }

    public function setTicket_type($value)
    {
        $this->ticket_type = $value;
    }

    public function getImage_airline()
    {
        return $this->image_airline;
    }

    public function setImage_airline($value)
    {
        $this->image_airline = $value;
    }

    public function getCity_image()
    {
        return $this->city_image;
    }

    public function setCity_image($value)
    {
        $this->city_image = $value;
    }

    public function getAgency_id()
    {
        return $this->agency_id;
    }

    public function setAgency_id($value)
    {
        $this->agency_id = $value;
    }

    public function getPromotion_id()
    {
        return $this->promotion_id;
    }

    public function setPromotion_id($value)
    {
        $this->promotion_id = $value;
    }
}
