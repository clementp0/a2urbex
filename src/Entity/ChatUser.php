<?php

namespace App\Entity;

use App\Repository\ChatUserRepository;
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

    #[Groups(['chatInfo'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $pseudo = null;

    #[Groups(['chatInfo'])]
    #[ORM\Column(nullable: true)]
    private ?bool $op = null;

    #[Groups(['chatInfo'])]
    #[ORM\ManyToOne(inversedBy: 'chatUsers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

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
}
