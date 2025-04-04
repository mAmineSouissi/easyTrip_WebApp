<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
class Res_transport
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "integer")]
    private int $user_id;

    #[ORM\Column(type: "integer")]
    private int $car_id;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $start_date;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $end_date;

    #[ORM\Column(type: "string", length: 255)]
    private string $status;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getUser_id()
    {
        return $this->user_id;
    }

    public function setUser_id($value)
    {
        $this->user_id = $value;
    }

    public function getCar_id()
    {
        return $this->car_id;
    }

    public function setCar_id($value)
    {
        $this->car_id = $value;
    }

    public function getStart_date()
    {
        return $this->start_date;
    }

    public function setStart_date($value)
    {
        $this->start_date = $value;
    }

    public function getEnd_date()
    {
        return $this->end_date;
    }

    public function setEnd_date($value)
    {
        $this->end_date = $value;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($value)
    {
        $this->status = $value;
    }
}
