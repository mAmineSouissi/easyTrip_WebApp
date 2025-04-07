<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
class Reclamation
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

    
    #[ORM\Column(type: "string", length: 15)]
    private string $status;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $date;

    #[ORM\Column(type: "string", length: 15)]
    private string $issue;

    #[ORM\Column(type: "string", length: 15)]
    private string $category;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function setUserId($value)
    {
        $this->userId = $value;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($value)
    {
        $this->status = $value;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setDate($value)
    {
        $this->date = $value;
    }

    public function getIssue()
    {
        return $this->issue;
    }

    public function setIssue($value)
    {
        $this->issue = $value;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function setCategory($value)
    {
        $this->category = $value;
    }
}
