<?php

namespace App\Entity;

use App\Repository\ReclamationRepository;
use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReclamationRepository::class)]
#[ORM\Table(name: "reclamation")]
class Reclamation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 15)]
    #[Assert\Choice(choices: ["En cours", "Fermée", "En attente"], message: "Le statut doit être 'En cours', 'Fermée' ou 'En attente'.")]
    private string $status;

    #[ORM\Column(type: "date")]
    #[Assert\NotNull(message: "La date est obligatoire.")]
    private \DateTimeInterface $date;

    #[ORM\Column(type: "string", length: 50)]
    #[Assert\NotBlank(message: "Le champ 'Problème' est obligatoire.")]
    #[Assert\Length(max: 50, maxMessage: "Le champ 'Problème' ne peut pas dépasser {{ limit }} caractères.")]
    private string $issue;

    #[ORM\Column(type: "string", length: 20)]
    #[Assert\NotBlank(message: "La catégorie est obligatoire.")]
    #[Assert\Choice(choices: ["Service client", "Paiement", "Problème technique", "Réservation", "Autre"], message: "Choisissez une catégorie valide.")]
    private string $category;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'reclamations')]
    #[ORM\JoinColumn(name: 'userId', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    // Getters et Setters

    public function getId(): ?int { return $this->id; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }

    public function getDate(): \DateTimeInterface { return $this->date; }
    public function setDate(\DateTimeInterface $date): self { $this->date = $date; return $this; }

    public function getIssue(): string { return $this->issue; }
    public function setIssue(string $issue): self { $this->issue = $issue; return $this; }

    public function getCategory(): string { return $this->category; }
    public function setCategory(string $category): self { $this->category = $category; return $this; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): self { $this->user = $user; return $this; }
}
