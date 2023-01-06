<?php

namespace App\Entity;

use App\Repository\ResumeDetailsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ResumeDetailsRepository::class)
 */
class ResumeDetails
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $Profile;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $Skills_dev;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $Skills_graphics;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $Diplomas;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $Title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $SubTitle;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="resumeDetails")
     */
    private $Lang;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProfile(): ?string
    {
        return $this->Profile;
    }

    public function setProfile(?string $Profile): self
    {
        $this->Profile = $Profile;

        return $this;
    }

    public function getSkillsDev(): ?string
    {
        return $this->Skills_dev;
    }

    public function setSkillsDev(?string $Skills_dev): self
    {
        $this->Skills_dev = $Skills_dev;

        return $this;
    }

    public function getSkillsGraphics(): ?string
    {
        return $this->Skills_graphics;
    }

    public function setSkillsGraphics(?string $Skills_graphics): self
    {
        $this->Skills_graphics = $Skills_graphics;

        return $this;
    }

    public function getDiplomas(): ?string
    {
        return $this->Diplomas;
    }

    public function setDiplomas(?string $Diplomas): self
    {
        $this->Diplomas = $Diplomas;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->Title;
    }

    public function setTitle(?string $Title): self
    {
        $this->Title = $Title;

        return $this;
    }

    public function getSubTitle(): ?string
    {
        return $this->SubTitle;
    }

    public function setSubTitle(?string $SubTitle): self
    {
        $this->SubTitle = $SubTitle;

        return $this;
    }

    public function getLang(): ?Category
    {
        return $this->Lang;
    }

    public function setLang(?Category $Lang): self
    {
        $this->Lang = $Lang;

        return $this;
    }
}
