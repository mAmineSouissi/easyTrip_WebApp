<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
#[ORM\Entity]
#[ORM\Table(name: 'service_reservation')]
class ServiceReservation
{
    #[ORM\Id]
    #[ORM\Column(name: 'id_servicereservation', type: 'integer')]
    #[ORM\GeneratedValue]
    private ?int $id_servicereservation = null;

    #[Assert\NotBlank(message: "Ce champs est obligatoire !")]
    #[ORM\Column(type: 'string')]
    private  $service;

    #[Assert\NotBlank(message: "Ce champs est obligatoire !")]
    #[ORM\Column(type: 'string')]
    private  $description;


    public function getIdServicereservation(): ?int
    {
        return $this->id_servicereservation;
    }

    public function getService(): ?string
    {
        return $this->service;
    }

    public function setService(string $service): self
    {
        $this->service = $service;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }
}