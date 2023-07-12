<?php

namespace App\Entity;

use App\Repository\ChatUserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ChatUserRepository::class)]
class ChatUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'chatUsers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Chat $chat = null;

    #[Groups(['chat', 'chatInfo'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $pseudo = null;

    #[Groups(['chat', 'chatInfo'])]
    #[ORM\Column(nullable: true)]
    private ?bool $op = null;

    #[Groups(['chatInfo'])]
    #[ORM\ManyToOne(inversedBy: 'chatUsers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\OneToMany(mappedBy: 'chatUser', targetEntity: Message::class)]
    private Collection $messages;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChat(): ?Chat
    {
        return $this->chat;
    }

    public function setChat(?Chat $chat): static
    {
        $this->chat = $chat;

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(?string $pseudo): static
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function isOp(): ?bool
    {
        return $this->op;
    }

    public function setOp(?bool $Op): static
    {
        $this->op = $Op;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

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
            $message->setChatUser($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getChatUser() === $this) {
                $message->setChatUser(null);
            }
        }

        return $this;
    }
}
