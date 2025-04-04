<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
class Promotion
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "string", length: 255)]
    private string $title;

    #[ORM\Column(type: "text")]
    private string $description;

    #[ORM\Column(type: "float")]
    private float $discount_percentage;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $valid_until;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($value)
    {
        $this->title = $value;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($value)
    {
        $this->description = $value;
    }

    public function getDiscount_percentage()
    {
        return $this->discount_percentage;
    }

    public function setDiscount_percentage($value)
    {
        $this->discount_percentage = $value;
    }

    public function getValid_until()
    {
        return $this->valid_until;
    }

    public function setValid_until($value)
    {
        $this->valid_until = $value;
    }
}
