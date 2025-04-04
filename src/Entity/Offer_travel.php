<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
class Offer_travel
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "string", length: 255)]
    private string $departure;

    #[ORM\Column(type: "string", length: 255)]
    private string $destination;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $departure_date;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $arrival_date;

    #[ORM\Column(type: "string", length: 50)]
    private string $hotelName;

    #[ORM\Column(type: "string", length: 255)]
    private string $flightName;

    #[ORM\Column(type: "string", length: 255)]
    private string $discription;

    #[ORM\Column(type: "string", length: 255)]
    private string $category;

    #[ORM\Column(type: "float")]
    private float $price;

    #[ORM\Column(type: "string", length: 255)]
    private string $image;

    #[ORM\Column(type: "integer")]
    private int $agency_id;

    #[ORM\Column(type: "integer")]
    private int $promotion_id;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getDeparture()
    {
        return $this->departure;
    }

    public function setDeparture($value)
    {
        $this->departure = $value;
    }

    public function getDestination()
    {
        return $this->destination;
    }

    public function setDestination($value)
    {
        $this->destination = $value;
    }

    public function getDeparture_date()
    {
        return $this->departure_date;
    }

    public function setDeparture_date($value)
    {
        $this->departure_date = $value;
    }

    public function getArrival_date()
    {
        return $this->arrival_date;
    }

    public function setArrival_date($value)
    {
        $this->arrival_date = $value;
    }

    public function getHotelName()
    {
        return $this->hotelName;
    }

    public function setHotelName($value)
    {
        $this->hotelName = $value;
    }

    public function getFlightName()
    {
        return $this->flightName;
    }

    public function setFlightName($value)
    {
        $this->flightName = $value;
    }

    public function getDiscription()
    {
        return $this->discription;
    }

    public function setDiscription($value)
    {
        $this->discription = $value;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function setCategory($value)
    {
        $this->category = $value;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function setPrice($value)
    {
        $this->price = $value;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setImage($value)
    {
        $this->image = $value;
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
