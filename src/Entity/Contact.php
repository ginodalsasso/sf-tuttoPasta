<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ContactRepository;
use Symfony\Component\Validator\Constraints\Length;

#[ORM\Entity(repositoryClass: ContactRepository::class)]
class Contact
{
    // ---------------------------------ATTRIBUTS--------------------------------- //
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    
    #[Assert\NotBlank(message: "Le nom ne peut pas être vide.")]
    #[Length(
        min: 2,
        max: 50,
        minMessage: "Le nom doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le nom ne peut pas contenir plus de {{ limit }} caractères."
    )]
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $name = null;


    #[Assert\NotBlank(message: "L'e-mail ne peut pas être vide.")]
    #[Assert\Email(message: "L'e-mail '{{ value }}' n'est pas un e-mail valide.")]
    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[Length(
        min: 2,
        max: 255,
    )]
    #[Assert\NotBlank(message: "Le message ne peut pas être vide.")]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $subject = null;


    #[Assert\NotBlank(message: "Le message ne peut pas être vide.")]
    #[Assert\Length(
        min: 5,
        minMessage: "Le message doit contenir au moins {{ limit }} caractères."
    )]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $message = null;


    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'contacts')]
    private ?User $user = null;

    // ---------------------------------CONSTRUCT--------------------------------- //


    public function __construct()
    {
        //initialise la date et l'heure du RDV lors de la création de l'objet
        $timezone = new \DateTimeZone('Europe/Paris');
        $this->createdAt = new \DateTime('now', $timezone);
    }


    // ---------------------------------GETTERS AND SETTERS--------------------------------- //


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
