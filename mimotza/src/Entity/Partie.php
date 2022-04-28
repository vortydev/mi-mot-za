<?php

namespace App\Entity;

use App\Repository\PartieRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PartieRepository::class)]
class Partie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'parties')]
    #[ORM\JoinColumn(nullable: false)]
    private $idUser;

    #[ORM\Column(type: 'boolean')]
    private $win;

    #[ORM\Column(type: 'smallint')]
    private $score;

    #[ORM\Column(type: 'time')]
    private $temps;

    #[ORM\Column(type: 'datetime')]
    private $dateEmission;

    #[ORM\ManyToOne(targetEntity: Mot::class, inversedBy: 'parties')]
    #[ORM\JoinColumn(nullable: false)]
    private $mot;

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

    public function getWin(): ?bool
    {
        return $this->win;
    }

    public function setWin(bool $win): self
    {
        $this->win = $win;

        return $this;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(int $score): self
    {
        $this->score = $score;

        return $this;
    }

    public function getTemps(): ?\DateTimeInterface
    {
        return $this->temps;
    }

    public function setTemps(\DateTimeInterface $temps): self
    {
        $this->temps = $temps;

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

    public function getMot(): ?Mot
    {
        return $this->mot;
    }

    public function setMot(?Mot $mot): self
    {
        $this->mot = $mot;

        return $this;
    }
}
