<?php

namespace App\Entity;

use App\Repository\ChatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Filesystem\Filesystem;

#[ORM\Entity(repositoryClass: ChatRepository::class)]
class Chat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['chat'])]
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'chat', targetEntity: Message::class, orphanRemoval: true)]
    private Collection $messages;
    
    #[Groups(['chat'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;
    
    #[Groups(['chat'])]
    #[ORM\Column(nullable: true)]
    private ?bool $multi = null;
    
    #[Groups(['chat'])]
    public $lastMessage;
    
    #[Groups(['chat'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;
    
    #[Groups(['chatInfo'])]
    #[ORM\OneToMany(mappedBy: 'chat', targetEntity: ChatUser::class, orphanRemoval: true, cascade: ["persist"])]
    private Collection $chatUsers;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
        $this->chatUsers = new ArrayCollection();
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

    /**
     * @return Collection<int, Message>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setChat($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getChat() === $this) {
                $message->setChat(null);
            }
        }

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function isMulti(): ?bool
    {
        return $this->multi;
    }

    public function setMulti(?bool $multi): self
    {
        $this->multi = $multi;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function setImageCustom(?string $string): static
    {
        [$data, $image] = explode(',', $string);
        preg_match('#image/(.*);#', $data, $matches);
        if(!count($matches) || !isset($matches[1])) return $this;

        $extension = $matches[1];
        $validExtensions = ['jpg', 'jpeg', 'png'];

        if (!in_array($extension, $validExtensions)) {
            throw new \Exception('Invalid file type');
        } else {
            $filename = md5(uniqid()) . '.' . $extension;
            $filepath = $this->getUploadDir() . $filename;
            
            $filesystem = new Filesystem();
            $filesystem->dumpFile($filepath, base64_decode($image));

            if($this->image) unlink($this->getPublicDir().$this->image);
            $this->image = $_ENV['IMG_CHAT_PATH'] . $filename;
        }        

        return $this;
    }

    private function getPublicDir() {
        return __DIR__ . '/../../public/';
    }
    private function getUploadDir()
    {
        return $this->getPublicDir().$_ENV['IMG_CHAT_PATH'];
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
            $chatUser->setChat($this);
        }

        return $this;
    }

    public function removeChatUser(ChatUser $chatUser): static
    {
        if ($this->chatUsers->removeElement($chatUser)) {
            // set the owning side to null (unless already changed)
            if ($chatUser->getChat() === $this) {
                $chatUser->setChat(null);
            }
        }

        return $this;
    }
}
