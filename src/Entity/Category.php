<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CategoryRepository::class)
 */
class Category
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
    private $name;

    /**
     * @ORM\OneToMany(targetEntity=ResumeEntity::class, mappedBy="Lang")
     */
    private $resumeEntities;

    /**
     * @ORM\OneToMany(targetEntity=ResumeDetails::class, mappedBy="Lang")
     */
    private $resumeDetails;

    public function __construct()
    {
        $this->ResumeEntitys = new ArrayCollection();
        $this->resumeEntities = new ArrayCollection();
        $this->resumeDetails = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getName();
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function addResumeEntity(ResumeEntity $ResumeEntity): self
    {
        if (!$this->ResumeEntitys->contains($ResumeEntity)) {
            $this->ResumeEntitys[] = $ResumeEntity;
            $ResumeEntity->setGroupement($this);
        }

        return $this;
    }

    public function removeResumeEntity(ResumeEntity $ResumeEntity): self
    {
        if ($this->ResumeEntitys->removeElement($ResumeEntity)) {
            // set the owning side to null (unless already changed)
            if ($ResumeEntity->getGroupement() === $this) {
                $ResumeEntity->setGroupement(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ResumeEntity>
     */
    public function getResumeEntities(): Collection
    {
        return $this->resumeEntities;
    }

    /**
     * @return Collection<int, ResumeDetails>
     */
    public function getResumeDetails(): Collection
    {
        return $this->resumeDetails;
    }

    public function addResumeDetail(ResumeDetails $resumeDetail): self
    {
        if (!$this->resumeDetails->contains($resumeDetail)) {
            $this->resumeDetails[] = $resumeDetail;
            $resumeDetail->setLang($this);
        }

        return $this;
    }

    public function removeResumeDetail(ResumeDetails $resumeDetail): self
    {
        if ($this->resumeDetails->removeElement($resumeDetail)) {
            // set the owning side to null (unless already changed)
            if ($resumeDetail->getLang() === $this) {
                $resumeDetail->setLang(null);
            }
        }

        return $this;
    }
}
