<?php

namespace App\Entity;


use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity]
#[ORM\Table(name: "reclamation")]
#[ORM\Index(name: "fok_user", columns: ["userId"])]
class Reclamation
{
    #[ORM\Id]

    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name: "id", type: "integer", nullable: false)]
    private ?int $id = null;

    #[ORM\Column(name: "status", type: "string", length: 15, nullable: false)]
    #[Assert\Choice(choices: ["En cours", "Fermée", "En attente"], message: "Le statut doit être 'En cours', 'Fermée' ou 'En attente'.")]

    private string $status;

    #[ORM\Column(name: "date", type: "date", nullable: false)]
    private \DateTimeInterface $date;

    #[ORM\Column(name: "issue", type: "string", length: 50, nullable: false)]
    #[Assert\NotBlank(message: "Le champ 'Problème' est obligatoire.")]
    #[Assert\Length(max: 50, maxMessage: "Le champ 'Problème' ne peut pas dépasser {{ limit }} caractères.")]
    private string $issue;

    #[ORM\Column(name: "category", type: "string", length: 15, nullable: false)]
    #[Assert\NotBlank(message: "La catégorie est obligatoire.")]
    #[Assert\Choice(choices: [
        "Service client",
        "Paiement",
        "Problème technique",
        "Réservation",
        "Autre"
    ], message: "Choisissez une catégorie valide.")]
    private string $category;
    

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "reclamations")]
    #[ORM\JoinColumn(name: "userId", referencedColumnName: "id", nullable: false)]
    private ?User $userid = null;

    // ---------------- GETTERS / SETTERS ----------------

    public function getId(): ?int { return $this->id; }
    public function getStatus(): ?string { return $this->status; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }

    public function getDate(): ?\DateTimeInterface { return $this->date; }
    public function setDate(\DateTimeInterface $date): self { $this->date = $date; return $this; }

    public function getIssue(): ?string { return $this->issue; }
    public function setIssue(string $issue): self { $this->issue = $issue; return $this; }

    public function getCategory(): ?string { return $this->category; }
    public function setCategory(string $category): self { $this->category = $category; return $this; }

    public function getUserid(): ?User { return $this->userid; }
    public function setUserid(?User $userid): self { $this->userid = $userid; return $this; }
}
