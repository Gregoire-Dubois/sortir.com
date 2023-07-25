<?php

namespace App\Entity;

use App\Repository\SortieRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=SortieRepository::class)
 */
class Sortie
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     *
     * @Assert\NotBlank(message="Le nom de la sortie est obligatoire")
     * @Assert\Length(
     *     min=3
     *     minMessage="Veuillez renseigner 3 caractères minimum"
     *     max=100
     *     maxMessage="Le nom de la sortie ne peut dépasser 100 caractères"
     * )
     */
    private $nom;

    /**
     * @ORM\Column(type="datetime")
     *
     * @Assert\NotBlank(message="La date et l'heure sont obligatoires")
     * @Assert\GreaterThan("today", message="La sortie doit au minimum avoir lieu demain ")
     */
    private $dateDebut;

    /**
     * @ORM\Column(type="integer")
     *
     * @Assert\NotBlank(message="La durée ne peut être vide")
     * @Assert\GreaterThanOrEqual(15, message="La durée mimimale d'une sortie est de 15 minutes")
     */
    private $duree;

    /**
     * @ORM\Column(type="datetime")
     *
     * @Assert\NotBlank(message="La date limite d'inscription est obligatoire")
     * @Assert\LessThanOrEqual(propertyPath="dateDebut", message="La date limite d'inscription doit être inférieure ou égale à la date de sortie")
     */
    private $dateLimiteInscription;

    /**
     * @ORM\Column(type="integer")
     *
     * @Assert\NotBlank(message="Veuillez indiquez un nombre maximum de participants")
     * @Assert\LessThanOrEqual(500, message="Le nombre maximum de participants ne peut exéder 500 personnes")
     */
    private $nbInscritptionMax;

    /**
     * @ORM\Column(type="text")
     *
     */
    private $description;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $motif;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateCreation;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateModification;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $modifiePar;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): self
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(int $duree): self
    {
        $this->duree = $duree;

        return $this;
    }

    public function getDateLimiteInscription(): ?\DateTimeInterface
    {
        return $this->dateLimiteInscription;
    }

    public function setDateLimiteInscription(\DateTimeInterface $dateLimiteInscription): self
    {
        $this->dateLimiteInscription = $dateLimiteInscription;

        return $this;
    }

    public function getNbInscritptionMax(): ?int
    {
        return $this->nbInscritptionMax;
    }

    public function setNbInscritptionMax(int $nbInscritptionMax): self
    {
        $this->nbInscritptionMax = $nbInscritptionMax;

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

    public function getMotif(): ?string
    {
        return $this->motif;
    }

    public function setMotif(?string $motif): self
    {
        $this->motif = $motif;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getDateModification(): ?\DateTimeInterface
    {
        return $this->dateModification;
    }

    public function setDateModification(?\DateTimeInterface $dateModification): self
    {
        $this->dateModification = $dateModification;

        return $this;
    }

    public function getModifiePar(): ?string
    {
        return $this->modifiePar;
    }

    public function setModifiePar(?string $modifiePar): self
    {
        $this->modifiePar = $modifiePar;

        return $this;
    }
}
