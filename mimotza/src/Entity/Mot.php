<?php

namespace App\Entity;

use App\Repository\MotRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Langue;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MotRepository::class)]
class Mot
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;


    #[ORM\Column(type: 'string', length: 5)]
    private $mot;

    #[ORM\ManyToOne(targetEntity: Langue::class, inversedBy: 'mots')]
    #[ORM\JoinColumn(nullable: false)]
    private $idLangue;

    #[ORM\Column(type: 'datetime')]
    private $dateAjout;

    #[ORM\OneToMany(mappedBy: 'mot', targetEntity: Partie::class)]
    private $parties;

    #[ORM\OneToMany(mappedBy: 'mot', targetEntity: MotsJeu::class)]
    private $motsJeux;

    public function __construct()
    {
        $this->parties = new ArrayCollection();
        $this->motsJeux = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMot(): ?string
    {
        return $this->mot;
    }

    public function setMot(string $mot): self
    {
        $this->mot = $mot;

        return $this;
    }

    public function getIdLangue(): ?Langue
    {
        return $this->idLangue;
    }

    public function setIdLangue(?Langue $idLangue): self
    {
        $this->idLangue = $idLangue;

        return $this;
    }

    public function getDateAjout(): ?\DateTimeInterface
    {
        return $this->dateAjout;
    }

    public function setDateAjout(\DateTimeInterface $dateAjout): self
    {
        $this->dateAjout = $dateAjout;

        return $this;
    }

    /**
     * @return Collection<int, Partie>
     */
    public function getParties(): Collection
    {
        return $this->parties;
    }

    public function addParty(Partie $party): self
    {
        if (!$this->parties->contains($party)) {
            $this->parties[] = $party;
            $party->setMot($this);
        }

        return $this;
    }

    public function removeParty(Partie $party): self
    {
        if ($this->parties->removeElement($party)) {
            // set the owning side to null (unless already changed)
            if ($party->getMot() === $this) {
                $party->setMot(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, MotsJeu>
     */
    public function getMotsJeux(): Collection
    {
        return $this->motsJeux;
    }

    public function addMotsJeux(MotsJeu $motsJeux): self
    {
        if (!$this->motsJeux->contains($motsJeux)) {
            $this->motsJeux[] = $motsJeux;
            $motsJeux->setMot($this);
        }

        return $this;
    }

    public function removeMotsJeux(MotsJeu $motsJeux): self
    {
        if ($this->motsJeux->removeElement($motsJeux)) {
            // set the owning side to null (unless already changed)
            if ($motsJeux->getMot() === $this) {
                $motsJeux->setMot(null);
            }
        }

        return $this;
    }
}
