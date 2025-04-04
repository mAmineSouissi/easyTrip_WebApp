<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Survey;

#[ORM\Entity]
class SurveyResponse
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

        #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "surveyresponses")]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $user_id;

        #[ORM\ManyToOne(targetEntity: Survey::class, inversedBy: "surveyresponses")]
    #[ORM\JoinColumn(name: 'survey_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Survey $survey_id;

    #[ORM\Column(type: "text")]
    private string $response_data;

    #[ORM\Column(type: "text")]
    private string $recommendations;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $completed_at;

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

    public function getSurvey_id()
    {
        return $this->survey_id;
    }

    public function setSurvey_id($value)
    {
        $this->survey_id = $value;
    }

    public function getResponse_data()
    {
        return $this->response_data;
    }

    public function setResponse_data($value)
    {
        $this->response_data = $value;
    }

    public function getRecommendations()
    {
        return $this->recommendations;
    }

    public function setRecommendations($value)
    {
        $this->recommendations = $value;
    }

    public function getCompleted_at()
    {
        return $this->completed_at;
    }

    public function setCompleted_at($value)
    {
        $this->completed_at = $value;
    }
}
