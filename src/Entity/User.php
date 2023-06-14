<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(type: 'string')]
    private ?string $password = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $firstname = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $lastname = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $ProfilePicture = null;

    #[ORM\ManyToMany(targetEntity: Favorite::class, mappedBy: 'users')]
    private Collection $favorites;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $lastActiveAt = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Location::class)]
    private Collection $locations;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Friend::class)]
    private Collection $friends;

    #[ORM\OneToMany(mappedBy: 'friend', targetEntity: Friend::class)]
    private Collection $friendRequests;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?WebsocketToken $websocketToken = null;

    #[ORM\ManyToMany(targetEntity: Channel::class, mappedBy: 'users')]
    private Collection $channels;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $about = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $youtube = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tiktok = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $instagram = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $image = null;

    public $imageError;
    public $previousImage;
    public $previousBanner;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $banner = null;

    #[ORM\ManyToMany(targetEntity: Chat::class, mappedBy: 'users')]
    private Collection $chats;

    // #[ORM\OneToMany(mappedBy: 'user', targetEntity: Favorite::class, orphanRemoval: true)]
    // private Collection $favorites;

    public function __construct()
    {
        $this->favorites = new ArrayCollection();
        $this->locations = new ArrayCollection();
        $this->friends = new ArrayCollection();
        $this->friendRequests = new ArrayCollection();
        $this->channels = new ArrayCollection();
        $this->chats = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function hasRole($role)
    {
        $roles = $this->roles;
        return in_array($role, $roles);
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }


    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }


    public function __toString(): string
    {
        return (string) $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getProfilePicture(): ?string
    {
        return $this->ProfilePicture;
    }

    public function setProfilePicture(string $ProfilePicture): self
    {
        $this->ProfilePicture = $ProfilePicture;

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
            $favorite->addUser($this);
        }

        return $this;
    }

    public function removeFavorite(Favorite $favorite): self
    {
        if ($this->favorites->removeElement($favorite)) {
            $favorite->removeUser($this);
        }

        return $this;
    }

    public function getLastActiveAt(): ?\DateTime
    {
        return $this->lastActiveAt;
    }

    public function setLastActiveAt(?\DateTime $lastActiveAt): self
    {
        $this->lastActiveAt = $lastActiveAt;

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
            $location->setUser($this);
        }

        return $this;
    }

    public function removeLocation(Location $location): self
    {
        if ($this->locations->removeElement($location)) {
            // set the owning side to null (unless already changed)
            if ($location->getUser() === $this) {
                $location->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Friend>
     */
    public function getFriends(): Collection
    {
        return $this->friends;
    }

    public function addFriend(Friend $friend): self
    {
        if (!$this->friends->contains($friend)) {
            $this->friends->add($friend);
            $friend->setUser($this);
        }

        return $this;
    }

    public function removeFriend(Friend $friend): self
    {
        if ($this->friends->removeElement($friend)) {
            // set the owning side to null (unless already changed)
            if ($friend->getUser() === $this) {
                $friend->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Friend[]
     */
    public function getFriendRequests(): Collection
    {
        return $this->friendRequests;
    }

    public function addFriendRequest(Friend $friendRequest): self
    {
        if (!$this->friendRequests->contains($friendRequest)) {
            $this->friendRequests[] = $friendRequest;
            $friendRequest->setFriend($this);
        }

        return $this;
    }

    public function removeFriendRequest(Friend $friendRequest): self
    {
        if ($this->friendRequests->contains($friendRequest)) {
            $this->friendRequests->removeElement($friendRequest);
            // set the owning side to null (unless already changed)
            if ($friendRequest->getFriend() === $this) {
                $friendRequest->setFriend(null);
            }
        }

        return $this;
    }

    public function getWebsocketToken(): ?WebsocketToken
    {
        return $this->websocketToken;
    }

    public function setWebsocketToken(WebsocketToken $websocketToken): self
    {
        // set the owning side of the relation if necessary
        if ($websocketToken->getUser() !== $this) {
            $websocketToken->setUser($this);
        }

        $this->websocketToken = $websocketToken;

        return $this;
    }

    /**
     * @return Collection<int, Channel>
     */
    public function getChannels(): Collection
    {
        return $this->channels;
    }

    public function addChannel(Channel $channel): self
    {
        if (!$this->channels->contains($channel)) {
            $this->channels->add($channel);
            $channel->addUser($this);
        }

        return $this;
    }

    public function removeChannel(Channel $channel): self
    {
        if ($this->channels->removeElement($channel)) {
            $channel->removeUser($this);
        }

        return $this;
    }

    public function getAbout(): ?string
    {
        return $this->about;
    }

    public function setAbout(?string $about): self
    {
        $this->about = $about;

        return $this;
    }

    public function getYoutube(): ?string
    {
        return $this->youtube;
    }

    public function setYoutube(?string $youtube): self
    {
        $this->youtube = $youtube;

        return $this;
    }

    public function getTiktok(): ?string
    {
        return $this->tiktok;
    }

    public function setTiktok(?string $tiktok): self
    {
        $this->tiktok = $tiktok;

        return $this;
    }

    public function getInstagram(): ?string
    {
        return $this->instagram;
    }

    public function setInstagram(?string $instagram): self
    {
        $this->instagram = $instagram;

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

    public function getBanner(): ?string
    {
        return $this->banner;
    }
    public function getPreviousBanner(): ?string {
        return $this->previousBanner;
    }

    public function setBannerDirect($filename):self {
        $this->banner = $filename;

        return $this;
    }

    public function setBanner(?\Symfony\Component\HttpFoundation\File\UploadedFile $file): self
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
                $this->banner = $filename;

                if($this->previousBanner && strpos($this->previousBanner, '..') !== false && file_exists($this->getPublicDir().$this->previousBanner)) {
                    unlink($this->getPublicDir().$this->previousBanner);
                }
            }
        }
    
        return $this;
    }
    public function removeBanner(): self {
        $this->banner = null;
        return $this;
    }

    /**
     * @return Collection<int, Chat>
     */
    public function getChats(): Collection
    {
        return $this->chats;
    }

    public function addChat(Chat $chat): self
    {
        if (!$this->chats->contains($chat)) {
            $this->chats->add($chat);
            $chat->addUser($this);
        }

        return $this;
    }

    public function removeChat(Chat $chat): self
    {
        if ($this->chats->removeElement($chat)) {
            $chat->removeUser($this);
        }

        return $this;
    }
}
