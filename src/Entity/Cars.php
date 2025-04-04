<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
class Cars
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "string", length: 255)]
    private string $model;

    #[ORM\Column(type: "integer")]
    private int $seats;

    #[ORM\Column(type: "string", length: 255)]
    private string $location;

    #[ORM\Column(type: "float")]
    private float $price_per_day;

    #[ORM\Column(type: "string", length: 255)]
    private string $image;

    #[ORM\Column(type: "string", length: 255)]
    private string $availability;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getModel()
    {
        return $this->model;
    }

    public function setModel($value)
    {
        $this->model = $value;
    }

    public function getSeats()
    {
        return $this->seats;
    }

    public function setSeats($value)
    {
        $this->seats = $value;
    }

    public function getLocation()
    {
        return $this->location;
    }

    public function setLocation($value)
    {
        $this->location = $value;
    }

    public function getPrice_per_day()
    {
        return $this->price_per_day;
    }

    public function setPrice_per_day($value)
    {
        $this->price_per_day = $value;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setImage($value)
    {
        $this->image = $value;
    }

    public function getAvailability()
    {
        return $this->availability;
    }

    public function setAvailability($value)
    {
        $this->availability = $value;
    }
}
