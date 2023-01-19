<?php

namespace App\Entity;

use Stringable;
use App\Repository\TypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TypeRepository::class)]
class Type implements Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'type', targetEntity: TypeOption::class)]
    private Collection $typeOptions;

    #[ORM\OneToMany(mappedBy: 'type', targetEntity: Location::class)]
    private Collection $locations;

    #[ORM\Column(length: 255)]
    private ?string $icon = null;

    public function __construct()
    {
        $this->typeOptions = new ArrayCollection();
        $this->locations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return (string) $this->name;
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

    /**
     * @return Collection<int, TypeOption>
     */
    public function getTypeOptions(): Collection
    {
        return $this->typeOptions;
    }

    public function addTypeOption(TypeOption $typeOption): self
    {
        if (!$this->typeOptions->contains($typeOption)) {
            $this->typeOptions->add($typeOption);
            $typeOption->setType($this);
        }

        return $this;
    }

    public function removeTypeOption(TypeOption $typeOption): self
    {
        if ($this->typeOptions->removeElement($typeOption)) {
            // set the owning side to null (unless already changed)
            if ($typeOption->getType() === $this) {
                $typeOption->setType(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Location>
     */
    public function getLocations(): Collection
    {
        return $this->locations;
    }

    public function addLocation(Location $location): self
    {
        if (!$this->locations->contains($location)) {
            $this->locations->add($location);
            $location->setType($this);
        }

        return $this;
    }

    public function removeLocation(Location $location): self
    {
        if ($this->locations->removeElement($location)) {
            // set the owning side to null (unless already changed)
            if ($location->getType() === $this) {
                $location->setType(null);
            }
        }

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }
}
