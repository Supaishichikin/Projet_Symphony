<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FriendshipRepository")
 */
class Friendship
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="requestedFriendships")
     * @ORM\JoinColumn(nullable=false)
     */
    private $requestingUser;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="friendshipRequests")
     * @ORM\JoinColumn(nullable=false)
     */
    private $requestedUser;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $status;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRequestingUser(): ?User
    {
        return $this->requestingUser;
    }

    public function setRequestingUser(?User $requestingUser): self
    {
        $this->requestingUser = $requestingUser;

        return $this;
    }

    public function getRequestedUser(): ?User
    {
        return $this->requestedUser;
    }

    public function setRequestedUser(?User $requestedUser): self
    {
        $this->requestedUser = $requestedUser;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }
}
