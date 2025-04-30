<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Panier;
use App\Entity\Agency; // Add this import for the Agency entity
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
#[ORM\Table(name: "User")]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "string", length: 255)]
    private string $name;

    #[ORM\Column(type: "string", length: 255)]
    private string $surname;

    #[ORM\Column(type: "string", length: 255)]
    private string $password;

    #[ORM\Column(type: "string", length: 255)]
    private string $email;

    #[ORM\Column(type: "string", length: 20)]
    private string $phone;

    #[ORM\Column(type: "string", length: 255)]
    private string $addresse;

    #[ORM\Column(type: "string", length: 255)]
    private string $profilePhoto;

    #[ORM\Column(type: "string", length: 255)]
    private string $role;

    #[ORM\OneToMany(mappedBy: "user_id", targetEntity: Reservation::class)]
    private Collection $reservations;

    #[ORM\OneToMany(mappedBy: "created_by", targetEntity: Survey::class)]
    private Collection $surveys;

    #[ORM\OneToMany(mappedBy: "user_id", targetEntity: Panier::class)]
    private Collection $paniers;

    // Add OneToMany relationship with Agency
    #[ORM\OneToMany(mappedBy: "user", targetEntity: Agency::class)]
    private Collection $agencies;

    public function __construct()
    {
        $this->reservations = new ArrayCollection();
        $this->surveys = new ArrayCollection();
        $this->paniers = new ArrayCollection();
        $this->agencies = new ArrayCollection(); // Initialize the agencies collection
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($value)
    {
        $this->name = $value;
    }

    public function getSurname()
    {
        return $this->surname;
    }

    public function setSurname($value)
    {
        $this->surname = $value;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getUserIdentifier(): string
    {
        return $this->email; 
    }

    public function setPassword($value)
    {
        $this->password = $value;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($value)
    {
        $this->email = $value;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function setPhone($value)
    {
        $this->phone = $value;
    }

    public function getAddresse()
    {
        return $this->addresse;
    }

    public function setAddresse($value)
    {
        $this->addresse = $value;
    }

    public function getProfilePhoto()
    {
        return $this->profilePhoto;
    }

    public function setProfilePhoto($value)
    {
        $this->profilePhoto = $value;
    }

    public function getRoles(): array
    {
        $role = strtoupper($this->role); 
        $symfonyRole = 'ROLE_' . $role; 
    
        // Always add ROLE_USER as a default role
        return array_unique([$symfonyRole, 'ROLE_USER']);
    }

    public function getRole()
    {
        return $this->role;
    }

    public function eraseCredentials()
    {
        // If you store any temporary sensitive data, clear it here
    }

    public function setRole($value)
    {
        $this->role = $value;
    }

    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): self
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations[] = $reservation;
            $reservation->setUser_id($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getUser_id() === $this) {
                $reservation->setUser_id(null);
            }
        }

        return $this;
    }

    public function getSurveys(): Collection
    {
        return $this->surveys;
    }

    public function addSurvey(Survey $survey): self
    {
        if (!$this->surveys->contains($survey)) {
            $this->surveys[] = $survey;
            $survey->setCreated_by($this);
        }

        return $this;
    }

    public function removeSurvey(Survey $survey): self
    {
        if ($this->surveys->removeElement($survey)) {
            // set the owning side to null (unless already changed)
            if ($survey->getCreated_by() === $this) {
                $survey->setCreated_by(null);
            }
        }

        return $this;
    }

    // Add the getAgencies method
    public function getAgencies(): Collection
    {
        return $this->agencies;
    }

    // Add method to add an agency
    public function addAgency(Agency $agency): self
    {
        if (!$this->agencies->contains($agency)) {
            $this->agencies[] = $agency;
            $agency->setUser($this);
        }

        return $this;
    }

    // Add method to remove an agency
    public function removeAgency(Agency $agency): self
    {
        if ($this->agencies->removeElement($agency)) {
            // set the owning side to null (unless already changed)
            if ($agency->getUser() === $this) {
                $agency->setUser(null);
            }
        }

        return $this;
    }
}