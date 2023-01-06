<?php

namespace App\Entity;

use App\Repository\ResumeEntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass=ResumeEntityRepository::class)
 */
class ResumeEntity
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $compagny;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $datefrom;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateto;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="resumeEntities")
     * @ORM\JoinColumn(nullable=false)
     */
    private $Lang;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $duration;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCompagny(): ?string
    {
        return $this->compagny;
    }

    public function setCompagny(string $compagny): self
    {
        $this->compagny = $compagny;

        return $this;
    }

    public function getDatefrom(): ?\DateTimeInterface
    {
        return $this->datefrom;
    }

    public function setDatefrom(?\DateTimeInterface $datefrom): self
    {
        $this->datefrom = $datefrom;

        return $this;
    }

    public function getDateto(): ?\DateTimeInterface
    {
        return $this->dateto;
    }

    public function setDateto(?\DateTimeInterface $dateto): self
    {
        $this->dateto = $dateto;

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

    public function getDuration(): ?string
    {
        return $this->duration;
    }

    public function setDuration(?string $duration): self
    {
        $this->duration = $duration;

        return $this;
    }
}
