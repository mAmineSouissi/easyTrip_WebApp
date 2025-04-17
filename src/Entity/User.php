<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class User implements PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Name cannot be blank")]
    private string $name;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Surname cannot be blank")]
    private string $surname;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Password cannot be blank")]
    private string $password;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Email cannot be blank")]
    #[Assert\Email(message: "Please enter a valid email address.")]
    private string $email;

    #[ORM\Column(type: "string", length: 20)]
    #[Assert\NotBlank(message: "Phone number cannot be blank")]
    #[Assert\Regex(
        pattern: "/^[0-9]{10}$/",
        message: "Please enter a valid phone number with 10 digits."
    )]
    private string $phone;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Address cannot be blank")]
    private string $addresse;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Profile photo cannot be blank")]
    private string $profilePhoto;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Role cannot be blank")]
    private string $role;

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

    public function getRole()
    {
        return $this->role;
    }

    public function setRole($value)
    {
        $this->role = $value;
    }

    #[ORM\OneToMany(mappedBy: "user_id", targetEntity: Reservation::class)]
    private Collection $reservations;

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

    #[ORM\OneToMany(mappedBy: "created_by", targetEntity: Survey::class)]
    private Collection $surveys;

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

    #[ORM\OneToMany(mappedBy: "user", targetEntity: Agency::class)]
    private Collection $agencies;

 

public function getAgencies(): Collection
{
    return $this->agencies;
}

public function addAgency(Agency $agency): self
{
    if (!$this->agencies->contains($agency)) {
        $this->agencies[] = $agency;
        $agency->setUser($this);
    }
    return $this;
}

public function removeAgency(Agency $agency): self
{
    if ($this->agencies->removeElement($agency)) {
        // set the owning side to null (unless already changed)
        if ($agency->getUser() === $this) {
            $agency->setUser(null);
        }
    }
    return $this;}

    #[ORM\OneToMany(mappedBy: "userId", targetEntity: Feedback::class)]
    private Collection $feedbacks;

    public function getFeedbacks(): Collection
    {
        return $this->feedbacks;
    }

    public function addFeedback(Feedback $feedback): self
    {
        if (!$this->feedbacks->contains($feedback)) {
            $this->feedbacks[] = $feedback;
            $feedback->setUserId($this);
        }

        return $this;
    }

    public function removeFeedback(Feedback $feedback): self
    {
        if ($this->feedbacks->removeElement($feedback)) {
            // set the owning side to null (unless already changed)
            if ($feedback->getUserId() === $this) {
                $feedback->setUserId(null);
            }
        }

        return $this;
    }

    #[ORM\OneToMany(mappedBy: "user_id", targetEntity: Hotels::class)]
    private Collection $hotelss;

    #[ORM\OneToMany(mappedBy: "userId", targetEntity: Reclamation::class)]
    private Collection $reclamations;

    #[ORM\OneToMany(mappedBy: "user_id", targetEntity: Tickets::class)]
    private Collection $ticketss;

    #[ORM\OneToMany(mappedBy: "user_id", targetEntity: Panier::class)]
    private Collection $paniers;

    
}