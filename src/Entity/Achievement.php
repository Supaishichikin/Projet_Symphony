<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AchievementRepository")
 */
class Achievement
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     * @Assert\NotBlank(message="le titre est obligatoire")
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="achievements")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank(message="La catégorie est obligatoire")
     */
    private $category;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserAchievement", mappedBy="achievement", orphanRemoval=true)
     */
    private $userAchievements;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Assert\File(mimeTypes={"image/png", "image/jpeg"},
     *     mimeTypesMessage="Le fichier doit être un JPG ou un PNG",
     *     maxSize="600k",
     *     maxSizeMessage="Le fichier ne doit pas dépasser {{ limit }} Mo")
     */
    private $image;

    public function __construct()
    {
        $this->userAchievements = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCategory(): ?category
    {
        return $this->category;
    }

    public function setCategory(?category $category): self
    {
        $this->category = $category;

        return $this;
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
            $userAchievement->setAchievement($this);
        }

        return $this;
    }

    public function removeUserAchievement(UserAchievement $userAchievement): self
    {
        if ($this->userAchievements->contains($userAchievement)) {
            $this->userAchievements->removeElement($userAchievement);
            // set the owning side to null (unless already changed)
            if ($userAchievement->getAchievement() === $this) {
                $userAchievement->setAchievement(null);
            }
        }

        return $this;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setImage($image): self
    {
        $this->image = $image;

        return $this;
    }
}
