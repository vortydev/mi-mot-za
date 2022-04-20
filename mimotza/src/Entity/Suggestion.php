<?php

namespace App\Entity;

use App\Repository\SuggestionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SuggestionRepository::class)]
class Suggestion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'suggestions')]
    #[ORM\JoinColumn(nullable: false)]
    private $idUser;

    #[ORM\Column(type: 'string', length: 5)]
    private $motSuggere;

    #[ORM\ManyToOne(targetEntity: Langue::class, inversedBy: 'suggestions')]
    #[ORM\JoinColumn(nullable: false)]
    private $idLangue;

    #[ORM\ManyToOne(targetEntity: EtatSuggestion::class, inversedBy: 'suggestions')]
    #[ORM\JoinColumn(nullable: false)]
    private $idEtatSuggestion;

    #[ORM\Column(type: 'datetime')]
    private $dateEmission;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdUser(): ?Utilisateur
    {
        return $this->idUser;
    }

    public function setIdUser(?Utilisateur $idUser): self
    {
        $this->idUser = $idUser;

        return $this;
    }

    public function getMotSuggere(): ?string
    {
        return $this->motSuggere;
    }

    public function setMotSuggere(string $motSuggere): self
    {
        $this->motSuggere = $motSuggere;

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

    public function getIdEtatSuggestion(): ?EtatSuggestion
    {
        return $this->idEtatSuggestion;
    }

    public function setIdEtatSuggestion(?EtatSuggestion $idEtatSuggestion): self
    {
        $this->idEtatSuggestion = $idEtatSuggestion;

        return $this;
    }

    public function getDateEmission(): ?\DateTimeInterface
    {
        return $this->dateEmission;
    }

    public function setDateEmission(\DateTimeInterface $dateEmission): self
    {
        $this->dateEmission = $dateEmission;

        return $this;
    }
}
