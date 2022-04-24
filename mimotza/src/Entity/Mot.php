<?php

namespace App\Entity;

use App\Repository\MotRepository;
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
}
