<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
class Feedback
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

    
    #[ORM\Column(type: "integer")]
    private int $offerId;

    #[ORM\Column(type: "integer")]
    private int $rating;

    #[ORM\Column(type: "string", length: 50)]
    private string $message;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $date;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }


    public function getOfferId()
    {
        return $this->offerId;
    }

    public function setOfferId($value)
    {
        $this->offerId = $value;
    }

    public function getRating()
    {
        return $this->rating;
    }

    public function setRating($value)
    {
        $this->rating = $value;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($value)
    {
        $this->message = $value;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setDate($value)
    {
        $this->date = $value;
    }
}
