<?php

namespace App\Entity;

use App\Repository\MotsJeuRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MotsJeuRepository::class)]
class MotsJeu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Mot::class, inversedBy: 'motsJeux')]
    #[ORM\JoinColumn(nullable: false)]
    private $mot;

    #[ORM\Column(type: 'date')]
    private $date;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }
}
