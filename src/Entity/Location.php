<?php

namespace App\Entity;

use App\Repository\LocationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: LocationRepository::class)]
class Location
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'bigint', nullable: true)]
    private ?string $pid = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $url = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['map'])]
    private ?string $image = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['map'])]
    private ?float $lon = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['map'])]
    private ?float $lat = null;

    #[ORM\ManyToOne(inversedBy: 'locations')]
    private ?Country $country = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['map'])]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'locations')]
    #[Groups(['map'])]
    private ?Category $category = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToMany(targetEntity: Favorite::class, mappedBy: 'locations')]
    private Collection $favorites;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $comments = null;

    #[ORM\Column(nullable: true)]
    private ?bool $done = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $source = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['map'])]
    private ?bool $disabled = null;

    #[ORM\Column(nullable: true)]
    private ?bool $ai = null;

    #[ORM\ManyToOne(inversedBy: 'locations')]
    private ?User $user = null;

    #[Groups(['map'])]
    public ?string $lid = null;

    public $previousImage;

    #[ORM\Column(nullable: true)]
    private ?bool $pending = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_add = null;

    public function __construct()
    {
        $this->date_add = new \DateTime();
        $this->favorites = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPid(): ?string
    {
        return $this->pid;
    }

    public function setPid(string $pid): self
    {
        $this->pid = $pid;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }
    public function getPreviousImage(): ?string {
        return $this->previousImage;
    }

    public function setImageDirect($filename):self {
        $this->image = $filename;

        return $this;
    }

    public function setImage(?\Symfony\Component\HttpFoundation\File\UploadedFile $file): self
    {
        if ($file) {
            $validExtensions = ['jpg', 'jpeg', 'png'];
            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, $validExtensions)) {
                throw new \Exception('Invalid file type');
            } else {
                $filename = $_ENV['IMG_LOCATION_PATH'] . md5(uniqid()) . '.' . $extension;
                $file->move(
                    $this->getUploadDir(),
                    $filename
                );
                $this->image = $filename;

                if($this->previousImage && strpos($this->previousImage, '..') !== false && file_exists($this->getPublicDir().$this->previousImage)) {
                    unlink($this->getPublicDir().$this->previousImage);
                }
            }
        }
    
        return $this;
    }
    public function removeImage(): self {
        $this->image = null;
        return $this;
    }
    
    private function getPublicDir() {
        return __DIR__ . '/../../public/';
    }
    private function getUploadDir()
    {
        return $this->getPublicDir().$_ENV['IMG_LOCATION_PATH'];
    }

    public function getLon(): ?string
    {
        return $this->lon;
    }

    public function setLon(?string $lon): self
    {
        $this->lon = $lon;

        return $this;
    }

    public function getLat(): ?string
    {
        return $this->lat;
    }

    public function setLat(?string $lat): self
    {
        $this->lat = $lat;

        return $this;
    }

    public function getCountry(): ?Country
    {
        return $this->country;
    }

    public function setCountry(?Country $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, Favorite>
     */
    public function getFavorites(): Collection
    {
        return $this->favorites;
    }

    public function addFavorite(Favorite $favorite): self
    {
        if (!$this->favorites->contains($favorite)) {
            $this->favorites->add($favorite);
            $favorite->addLocation($this);
        }

        return $this;
    }

    public function removeFavorite(Favorite $favorite): self
    {
        if ($this->favorites->removeElement($favorite)) {
            $favorite->removeLocation($this);
        }

        return $this;
    }

    public function getComments(): ?string
    {
        return $this->comments;
    }

    public function setComments(?string $comments): self
    {
        $this->comments = $comments;

        return $this;
    }

    public function isDone(): ?bool
    {
        return $this->done;
    }

    public function setDone(?bool $done): self
    {
        $this->done = $done;

        return $this;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(?string $source): self
    {
        $this->source = $source;

        return $this;
    }

    public function isDisabled(): ?bool
    {
        return $this->disabled;
    }

    public function setDisabled(?bool $disabled): self
    {
        $this->disabled = $disabled;

        return $this;
    }

    public function isAi(): ?bool
    {
        return $this->ai;
    }

    public function setAi(?bool $ai): self
    {
        $this->ai = $ai;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function isPending(): ?bool
    {
        return $this->pending;
    }

    public function setPending(?bool $pending): self
    {
        $this->pending = $pending;

        return $this;
    }

    public function getDateAdd(): ?\DateTimeInterface
    {
        return $this->date_add;
    }

    public function setDateAdd(?\DateTimeInterface $date_add): static
    {
        $this->date_add = $date_add;

        return $this;
    }
}