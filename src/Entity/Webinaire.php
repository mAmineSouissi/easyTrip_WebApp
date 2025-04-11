<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Webinaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Hotels::class, inversedBy: "webinaires")]
    #[ORM\JoinColumn(name: "hotel_id", referencedColumnName: "id_hotel")]
    private ?Hotels $hotel = null;

    #[ORM\Column(type: "string", length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: "text")]
    private ?string $description = null;

    #[ORM\Column(type: "datetime")]
    private ?\DateTimeInterface $debutDateTime = null;

    #[ORM\Column(type: "datetime")]
    private ?\DateTimeInterface $finitDateTime = null;

    #[ORM\Column(type: "string", length: 255)]
    private ?string $link = null;

    #[ORM\Column(type: "string", length: 255)]
    private ?string $room_id = null;

    // Getters et Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHotel(): ?Hotels
    {
        return $this->hotel;
    }

    public function setHotel(?Hotels $hotel): self
    {
        $this->hotel = $hotel;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getDebutDateTime(): ?\DateTimeInterface
    {
        return $this->debutDateTime;
    }

    public function setDebutDateTime(\DateTimeInterface $debutDateTime): self
    {
        $this->debutDateTime = $debutDateTime;
        return $this;
    }

    public function getFinitDateTime(): ?\DateTimeInterface
    {
        return $this->finitDateTime;
    }

    public function setFinitDateTime(\DateTimeInterface $finitDateTime): self
    {
        $this->finitDateTime = $finitDateTime;
        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(string $link): self
    {
        $this->link = $link;
        return $this;
    }

    public function getRoomId(): ?string
    {
        return $this->room_id;
    }

    public function setRoomId(string $room_id): self
    {
        $this->room_id = $room_id;
        return $this;
    }
}