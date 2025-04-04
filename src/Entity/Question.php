<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Survey;
use Doctrine\Common\Collections\Collection;
use App\Entity\Option;

#[ORM\Entity]
class Question
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

        #[ORM\ManyToOne(targetEntity: Survey::class, inversedBy: "questions")]
    #[ORM\JoinColumn(name: 'survey_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Survey $survey_id;

    #[ORM\Column(type: "text")]
    private string $question_text;

    #[ORM\Column(type: "boolean")]
    private bool $is_active;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getSurvey_id()
    {
        return $this->survey_id;
    }

    public function setSurvey_id($value)
    {
        $this->survey_id = $value;
    }

    public function getQuestion_text()
    {
        return $this->question_text;
    }

    public function setQuestion_text($value)
    {
        $this->question_text = $value;
    }

    public function getIs_active()
    {
        return $this->is_active;
    }

    public function setIs_active($value)
    {
        $this->is_active = $value;
    }

    #[ORM\OneToMany(mappedBy: "question_id", targetEntity: Option::class)]
    private Collection $options;
}
