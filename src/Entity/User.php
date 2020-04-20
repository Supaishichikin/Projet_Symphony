<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=20, unique=true)
     * @Assert\NotBlank(message="Le pseudo est obligatoire")
     * @Assert\Length(min="3", minMessage="Le pseudo ne peut pas faire moins de {{ limit }} caractères de long",
     *                max="20", maxMessage="Le pseudo ne peut pas faire plus de {{ limit }} caractères de long")
     */
    private $pseudo;

    /**
     * @ORM\Column(type="string", length=50, unique=true)
     * @Assert\NotBlank(message="L'adresse email est obligatoire")
     * @Assert\Email(message="L'adresse email n'est pas valide")
     * @Assert\Length(max="50", maxMessage="L'adresse email ne peut dépasser 50 caractères")
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @var string|null
     * @Assert\NotBlank(message="Le mot de passe est obligatoire", groups={"registration"})
     * @Assert\Length(min="8", minMessage="Le mot de passe doit faire au moins 8 caractères de long",
     *                max="20", maxMessage="Le mot de passe ne doit pas déapsser 20 caractères")
     */
    private $plainPassword;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $role = 'ROLE_USER';

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserAchievement", mappedBy="user", orphanRemoval=true)
     */
    private $userAchievements;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Friendship", mappedBy="requestingUser", orphanRemoval=true)
     */
    private $requestedFriendships;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Friendship", mappedBy="requestedUser", orphanRemoval=true)
     */
    private $friendshipRequests;

    public function __construct()
    {
        $this->userAchievements = new ArrayCollection();
        $this->requestedFriendships = new ArrayCollection();
        $this->friendshipRequests = new ArrayCollection();
    }

    public function __toString(): ?string
    {
        return $this->pseudo;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): self
    {
        $this->pseudo = $pseudo;

        return $this;
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

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getRoles()
    {
        return [$this->role];
    }

    /**
     * @inheritDoc
     */
    public function getSalt()
    {
        // On laisse vide car le cryptage utilisé incorpore son propre grain de sel
    }

    /**
     * @inheritDoc
     */
    public function getUsername()
    {
        // On utilise pour l'instant que le pseudo comme identifiant, peut-être aussi l'email plus tard
        return $this->pseudo;
    }

    /**
     * @inheritDoc
     */
    public function eraseCredentials()
    {
        // Aucune donnée sensible à effacer pour le moment
    }

    /**
     * @return Collection|UserAchievement[]
     */
    public function getUserAchievements(): Collection
    {
        return $this->userAchievements;
    }

    public function addUserAchievement(UserAchievement $userAchievement): self
    {
        if (!$this->userAchievements->contains($userAchievement)) {
            $this->userAchievements[] = $userAchievement;
            $userAchievement->setUser($this);
        }

        return $this;
    }

    public function removeUserAchievement(UserAchievement $userAchievement): self
    {
        if ($this->userAchievements->contains($userAchievement)) {
            $this->userAchievements->removeElement($userAchievement);
            // set the owning side to null (unless already changed)
            if ($userAchievement->getUser() === $this) {
                $userAchievement->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Friendship[]
     */
    public function getRequestedFriendships(): Collection
    {
        return $this->requestedFriendships;
    }

    public function addRequestedFriendship(Friendship $requestedFriendship): self
    {
        if (!$this->requestedFriendships->contains($requestedFriendship)) {
            $this->requestedFriendships[] = $requestedFriendship;
            $requestedFriendship->setRequestingUser($this);
        }

        return $this;
    }

    public function removeRequestedFriendship(Friendship $requestedFriendship): self
    {
        if ($this->requestedFriendships->contains($requestedFriendship)) {
            $this->requestedFriendships->removeElement($requestedFriendship);
            // set the owning side to null (unless already changed)
            if ($requestedFriendship->getRequestingUser() === $this) {
                $requestedFriendship->setRequestingUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Friendship[]
     */
    public function getFriendshipRequests(): Collection
    {
        return $this->friendshipRequests;
    }

    public function addFriendshipRequest(Friendship $friendshipRequest): self
    {
        if (!$this->friendshipRequests->contains($friendshipRequest)) {
            $this->friendshipRequests[] = $friendshipRequest;
            $friendshipRequest->setRequestedUser($this);
        }

        return $this;
    }

    public function removeFriendshipRequest(Friendship $friendshipRequest): self
    {
        if ($this->friendshipRequests->contains($friendshipRequest)) {
            $this->friendshipRequests->removeElement($friendshipRequest);
            // set the owning side to null (unless already changed)
            if ($friendshipRequest->getRequestedUser() === $this) {
                $friendshipRequest->setRequestedUser(null);
            }
        }

        return $this;
    }
}
