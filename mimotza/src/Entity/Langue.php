<?php

namespace App\Entity;

use App\Repository\LangueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LangueRepository::class)]
class Langue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $langue;

    #[ORM\Column(type: 'datetime')]
    private $dateAjout;

    #[ORM\OneToMany(mappedBy: 'idLangue', targetEntity: Mot::class)]
    private $mots;

    #[ORM\OneToMany(mappedBy: 'idLangue', targetEntity: Suggestion::class)]
    private $suggestions;

    public function __construct()
    {
        $this->mots = new ArrayCollection();
        $this->suggestions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLangue(): ?string
    {
        return $this->langue;
    }

    public function setLangue(string $langue): self
    {
        $this->langue = $langue;

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
     * @return Collection<int, Mot>
     */
    public function getMots(): Collection
    {
        return $this->mots;
    }

    public function addMot(Mot $mot): self
    {
        if (!$this->mots->contains($mot)) {
            $this->mots[] = $mot;
            $mot->setIdLangue($this);
        }

        return $this;
    }

    public function removeMot(Mot $mot): self
    {
        if ($this->mots->removeElement($mot)) {
            // set the owning side to null (unless already changed)
            if ($mot->getIdLangue() === $this) {
                $mot->setIdLangue(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Suggestion>
     */
    public function getSuggestions(): Collection
    {
        return $this->suggestions;
    }

    public function addSuggestion(Suggestion $suggestion): self
    {
        if (!$this->suggestions->contains($suggestion)) {
            $this->suggestions[] = $suggestion;
            $suggestion->setIdLangue($this);
        }

        return $this;
    }

    public function removeSuggestion(Suggestion $suggestion): self
    {
        if ($this->suggestions->removeElement($suggestion)) {
            // set the owning side to null (unless already changed)
            if ($suggestion->getIdLangue() === $this) {
                $suggestion->setIdLangue(null);
            }
        }

        return $this;
    }
}
