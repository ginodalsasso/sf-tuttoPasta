<?php

namespace App\Entity;

use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\AppointmentRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: AppointmentRepository::class)]
#[UniqueEntity(fields:["startDate", "endDate"], message:"Ce créneau horraire est déjà pris.")]
#[ORM\UniqueConstraint(name: "unique_appointment", columns: ["start_date", "end_date"])]

class Appointment
{

    // ---------------------------------ATTRIBUTS--------------------------------- //
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Le prénom ne peut pas être vide.")]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: "Le prénom doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le prénom ne peut pas contenir plus de {{ limit }} caractères."
    )]
    private ?string $firstName = null;


    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Le nom ne peut pas être vide.")]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: "Le nom doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le nom ne peut pas contenir plus de {{ limit }} caractères."
    )]
    private ?string $name = null;


    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'e-mail ne peut pas être vide.")]
    #[Assert\Email(message: "L'e-mail '{{ value }}' n'est pas un e-mail valide.")]
    private ?string $email = null;


    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "Le message ne peut pas être vide.")]
    #[Assert\Length(
        min: 5,
        minMessage: "Le message doit contenir au moins {{ limit }} caractères."
    )]
    private ?string $message = null;


    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    //Callback permet de créer une contrainte personalisée
    #[Assert\Callback([Appointment::class, "notWeekend"])]
    #[Assert\When(
        expression: 'this.getEndDate() != null',
        constraints: [
            new Assert\LessThan(
                propertyPath: 'endDate',
                message: 'La date de fin doit se situer après la date de début !'
            )
        ]
    )]
    #[Assert\NotBlank(message: 'Veuillez sélectionner une date de début')]
    #[Assert\GreaterThanOrEqual(
        "today",
        message: "Veuillez sélectionner une date dans le présent !"
    )]
    private ?\DateTimeInterface $startDate = null;


    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $endDate = null;


    #[ORM\Column(nullable: true)]
    private ?array $status = null;


    /**
     * @var Collection<int, Service>
     */
    #[ORM\ManyToMany(targetEntity: Service::class, inversedBy: 'appointments', cascade: ["persist"])]
    private Collection $services;


    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;


    #[ORM\ManyToOne(inversedBy: 'appointments')]
    private ?User $user = null;


    #[ORM\OneToOne(mappedBy: 'appointments', cascade: ['persist', 'remove'])]
    private ?Quote $quote = null;

    
    // ---------------------------------CONSTRUCT--------------------------------- //


    public function __construct()
    {
        $this->services = new ArrayCollection();

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

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

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

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getStatus(): ?array
    {
        return $this->status;
    }

    public function setStatus(?array $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, Service>
     */
    public function getServices(): Collection
    {
        return $this->services;
    }

    public function addService(Service $service): static
    {
        if (!$this->services->contains($service)) {
            $this->services->add($service);
            $service->addAppointment($this);
        }

        return $this;
    }

    public function removeService(Service $service): static
    {
        if ($this->services->removeElement($service)) {
            $service->removeAppointment($this);
        }

        return $this;
    }

    public static function notWeekend($startDate)
    {
        // Définit les jours de weekend, c'est-à-dire dimanche (0) et samedi (6).
        $weekendDays = [0, 6];

        // Vérifie si $startDate est une instance de DateTimeInterface et si elle est un jour de weekend.
        if ($startDate instanceof DateTimeInterface && in_array($startDate->format('w'), $weekendDays)) {
            throw new \InvalidArgumentException('Les RDV ne peuvent pas être pris durant le weekend.');
        }
        return true;
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

    public function getQuote(): ?Quote
    {
        return $this->quote;
    }

    public function setQuote(?Quote $quote): static
    {
        // Définit l'entité Quote associée à ce rendez-vous
        if ($quote === null && $this->quote !== null) {
            $this->quote->setAppointments(null);
        }

        // Définit l'entité Quote associée à ce rendez-vous
        if ($quote !== null && $quote->getAppointments() !== $this) {
            $quote->setAppointments($this);
        }

        $this->quote = $quote;

        return $this;
    }

    public function __toString(): string
    {
        return $this->startDate->format('d/m/Y H:i') . ' - ' . $this->endDate->format('d/m/Y H:i');
    }

}
