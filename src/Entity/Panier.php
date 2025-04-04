<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Reservation;

#[ORM\Entity]
class Panier
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

        #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "paniers")]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $user_id;

        #[ORM\ManyToOne(targetEntity: Reservation::class, inversedBy: "paniers")]
    #[ORM\JoinColumn(name: 'reservation_id', referencedColumnName: 'id_reservation', onDelete: 'CASCADE')]
    private Reservation $reservation_id;

    #[ORM\Column(type: "integer")]
    private int $coupon_id;

    #[ORM\Column(type: "integer")]
    private int $quantity;

    #[ORM\Column(type: "float")]
    private float $total_price;

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

    public function getReservation_id()
    {
        return $this->reservation_id;
    }

    public function setReservation_id($value)
    {
        $this->reservation_id = $value;
    }

    public function getCoupon_id()
    {
        return $this->coupon_id;
    }

    public function setCoupon_id($value)
    {
        $this->coupon_id = $value;
    }

    public function getQuantity()
    {
        return $this->quantity;
    }

    public function setQuantity($value)
    {
        $this->quantity = $value;
    }

    public function getTotal_price()
    {
        return $this->total_price;
    }

    public function setTotal_price($value)
    {
        $this->total_price = $value;
    }
}
