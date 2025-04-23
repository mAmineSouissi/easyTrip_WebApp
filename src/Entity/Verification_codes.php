<?php

namespace App\Entity;

use App\Repository\Verification_codesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: Verification_codesRepository::class)]
#[ORM\Table(name: "verification_codes")]
class Verification_codes
{

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]  // Ensures auto-increment is applied
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $email;

    #[ORM\Column(type: 'string', length: 255)]
    private $code;

    #[ORM\Column(type: 'datetime')]
    private $created_at;

    #[ORM\Column(type: 'datetime')]
    private $expires_at;

    #[ORM\Column(type: 'integer', options: ["default" => 0])]
    private $used;

    public function getId()
    {
        return $this->id;
    }
    public function setId($value)
    {
        $this->id = $value;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($value)
    {
        $this->email = $value;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setCode($value)
    {
        $this->code = $value;
    }

    public function getCreated_at()
    {
        return $this->created_at;
    }

    public function setCreated_at($value)
    {
        $this->created_at = $value;
    }

    public function getExpires_at()
    {
        return $this->expires_at;
    }

    public function setExpires_at($value)
    {
        $this->expires_at = $value;
    }

    public function getUsed()
    {
        return $this->used;
    }

    public function setUsed($value)
    {
        $this->used = $value;
    }
}
