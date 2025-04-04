<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Hotels;

#[ORM\Entity]
class Webinaire
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

        #[ORM\ManyToOne(targetEntity: Hotels::class, inversedBy: "webinaires")]
    #[ORM\JoinColumn(name: 'hotel_id', referencedColumnName: 'id_hotel', onDelete: 'CASCADE')]
    private Hotels $hotel_id;

    #[ORM\Column(type: "string", length: 255)]
    private string $title;

    #[ORM\Column(type: "text")]
    private string $description;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $debutDateTime;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $finitDateTime;

    #[ORM\Column(type: "string", length: 255)]
    private string $link;

    #[ORM\Column(type: "string", length: 255)]
    private string $room_id;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getHotel_id()
    {
        return $this->hotel_id;
    }

    public function setHotel_id($value)
    {
        $this->hotel_id = $value;
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

    public function getDebutDateTime()
    {
        return $this->debutDateTime;
    }

    public function setDebutDateTime($value)
    {
        $this->debutDateTime = $value;
    }

    public function getFinitDateTime()
    {
        return $this->finitDateTime;
    }

    public function setFinitDateTime($value)
    {
        $this->finitDateTime = $value;
    }

    public function getLink()
    {
        return $this->link;
    }

    public function setLink($value)
    {
        $this->link = $value;
    }

    public function getRoom_id()
    {
        return $this->room_id;
    }

    public function setRoom_id($value)
    {
        $this->room_id = $value;
    }
}
