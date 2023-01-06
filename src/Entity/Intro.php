<?php

namespace App\Entity;

use App\Repository\IntroRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=IntroRepository::class)
 */
class Intro
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $hey;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $intro;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $baseline;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $resume;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHey(): ?string
    {
        return $this->hey;
    }

    public function setHey(?string $hey): self
    {
        $this->hey = $hey;

        return $this;
    }

    public function getIntro(): ?string
    {
        return $this->intro;
    }

    public function setIntro(?string $intro): self
    {
        $this->intro = $intro;

        return $this;
    }

    public function getBaseline(): ?string
    {
        return $this->baseline;
    }

    public function setBaseline(?string $baseline): self
    {
        $this->baseline = $baseline;

        return $this;
    }

    public function getResume(): ?string
    {
        return $this->resume;
    }

    public function setResume(?string $resume): self
    {
        $this->resume = $resume;

        return $this;
    }
}
