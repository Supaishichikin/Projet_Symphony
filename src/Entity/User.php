<?php

namespace App\Entity;

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
     * @ORM\Column(type="string", length=50)
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
     * @Assert\NotBlank(message="Le mot de passe est obligatoire")
     * @Assert\Regex("/^(?=.*[A-Z].*[A-Z])(?=.*[!@#$&*])(?=.*[0-9].*[0-9])(?=.*[a-z].*[a-z]).{8,20}/", message="Le mot de passe n'est pas conforme")
     */
    private $plainPassword;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $role = 'ROLE_USER';

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
}
