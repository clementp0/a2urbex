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
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['chat'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column(type: 'json')]
    #[Groups(['chat'])]
    private array $roles = [];

    #[ORM\Column(type: 'string')]
    private ?string $password = null;

    #[Groups(['chat'])]
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $firstname = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $lastname = null;

    #[Groups(['chat'])]
    #[SerializedName('lastname')]
    private ?string $abbreviatedLastname = null;

    #[Groups(['chat'])]
    private ?string $username = null;


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
    #[Groups(['chat'])]
    private ?string $image = null;

    public $previousImage;
    public $previousBanner;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $banner = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: ChatUser::class, orphanRemoval: true)]
    private Collection $chatUsers;

    public function __construct()
    {
        $this->favorites = new ArrayCollection();
        $this->locations = new ArrayCollection();
        $this->friends = new ArrayCollection();
        $this->friendRequests = new ArrayCollection();
        $this->channels = new ArrayCollection();
        $this->chatUsers = new ArrayCollection();
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

    public function getAbbreviatedLastname(): ?string
    {
        return mb_substr($this->lastname, 0, 1);
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->firstname . '#' . $this->id;
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
                $filename = $_ENV['IMG_USER_PATH'] . md5(uniqid()) . '.' . $extension;
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
        return $this->getPublicDir().$_ENV['IMG_USER_PATH'];
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
                $filename = $_ENV['IMG_USER_PATH'] . md5(uniqid()) . '.' . $extension;
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
     * @return Collection<int, ChatUser>
     */
    public function getChatUsers(): Collection
    {
        return $this->chatUsers;
    }

    public function addChatUser(ChatUser $chatUser): static
    {
        if (!$this->chatUsers->contains($chatUser)) {
            $this->chatUsers->add($chatUser);
            $chatUser->setUser($this);
        }

        return $this;
    }

    public function removeChatUser(ChatUser $chatUser): static
    {
        if ($this->chatUsers->removeElement($chatUser)) {
            // set the owning side to null (unless already changed)
            if ($chatUser->getUser() === $this) {
                $chatUser->setUser(null);
            }
        }

        return $this;
    }
}
