<?php

namespace App\Entity;

use App\Repository\FavoriteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FavoriteRepository::class)]
class Favorite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    private ?bool $master = null;

    #[ORM\Column(nullable: true)]
    private ?bool $share = null;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'favorites')]
    private Collection $users;

    #[ORM\ManyToMany(targetEntity: Location::class, inversedBy: 'favorites')]
    private Collection $locations;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->locations = new ArrayCollection();
    }

//     #[ORM\ManyToOne]
//     #[ORM\JoinColumn(nullable: false)]
//     private ?Location $location = null;

//     #[ORM\ManyToOne(inversedBy: 'favorites')]
//     #[ORM\JoinColumn(nullable: false)]
//     private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

//     public function getLocation(): ?Location
//     {
//         return $this->location;
//     }

//     public function setLocation(?Location $location): self
//     {
//         $this->location = $location;

//         return $this;
//     }

//     public function getUser(): ?User
//     {
//         return $this->user;
//     }

//     public function setUser(?User $user): self
//     {
//         $this->user = $user;

//         return $this;
//     }

public function getName(): ?string
{
    return $this->name;
}

public function setName(string $name): self
{
    $this->name = $name;

    return $this;
}

public function isMaster(): ?bool
{
    return $this->master;
}

public function setMaster(?bool $master): self
{
    $this->master = $master;

    return $this;
}

public function isShare(): ?bool
{
    return $this->share;
}

public function setShare(?bool $share): self
{
    $this->share = $share;

    return $this;
}

/**
 * @return Collection<int, User>
 */
public function getUsers(): Collection
{
    return $this->users;
}

public function addUser(User $user): self
{
    if (!$this->users->contains($user)) {
        $this->users->add($user);
    }

    return $this;
}

public function removeUser(User $user): self
{
    $this->users->removeElement($user);

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
    }

    return $this;
}

public function removeLocation(Location $location): self
{
    $this->locations->removeElement($location);

    return $this;
}
}
