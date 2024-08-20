<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\QuoteRepository;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: QuoteRepository::class)]
class Quote
{
    // ---------------------------------ATTRIBUTS--------------------------------- //
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\Column(length: 255)]
    private ?string $reference = null;


    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $quoteDate = null;


    #[ORM\Column(nullable: true)]
    private ?float $totalTTC = null;


    #[ORM\OneToOne(inversedBy: 'quote', cascade: ['persist', 'remove'])]
    private ?Appointment $appointments = null;


    #[ORM\Column(length: 255)]
    private ?string $customerName = null;


    #[ORM\Column(length: 255)]
    private ?string $customerEmail = null;


    #[ORM\Column(length: 255)]
    private ?string $customerFirstName = null;


    #[ORM\Column(length: 255)]
    private ?string $pdfContent = null;


    #[ORM\Column]
    private ?bool $status = null;


    // Constantes d'état du devis
    public const STATE_PENDING = 'En attente';
    public const STATE_IN_PROGRESS = 'En cours';
    public const STATE_COMPLETED = 'Payé';
    public const STATE_ARCHIVED = 'Archivé';

    
    // ---------------------------------GETTERS AND SETTERS--------------------------------- //

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }

    public function getQuoteDate(): ?\DateTimeInterface
    {
        return $this->quoteDate;
    }

    public function setQuoteDate(\DateTimeInterface $quoteDate): static
    {
        $this->quoteDate = $quoteDate;

        return $this;
    }
    public function getAppointments(): ?Appointment
    {
        return $this->appointments;
    }

    public function setAppointments(?Appointment $appointments): static
    {
        $this->appointments = $appointments;

        return $this;
    }

    public function getCustomerName(): ?string
    {
        return $this->customerName;
    }

    public function setCustomerName(string $customerName): static
    {
        $this->customerName = $customerName;

        return $this;
    }

    public function getCustomerEmail(): ?string
    {
        return $this->customerEmail;
    }

    public function setCustomerEmail(string $customerEmail): static
    {
        $this->customerEmail = $customerEmail;

        return $this;
    }

    public function getCustomerFirstName(): ?string
    {
        return $this->customerFirstName;
    }

    public function setCustomerFirstName(string $customerFirstName): static
    {
        $this->customerFirstName = $customerFirstName;

        return $this;
    }
    
    public function getTotalTTC(): ?float
    {
        return $this->totalTTC;
    }

    public function setTotalTTC(?float $totalTTC): static
    {
        $this->totalTTC = $totalTTC;

        return $this;
    }

    public function getPdfContent(): ?string
    {
        return $this->pdfContent;
    }

    public function setPdfContent(string $pdfContent): static
    {
        $this->pdfContent = $pdfContent;

        return $this;
    }

    private array $services = [];

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $state = null;


    public function getServices(): array
    {
        return $this->services;
    }

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function addService(Service $service): self
    {
        // Si le service n'est pas déjà présent dans le tableau
        if (!in_array($service, $this->services, true)) {
            // On l'ajoute
            $this->services[] = $service;
        }

        return $this;
    }

    public function clearServices(): self
    {
        $this->services = [];

        return $this;
    }

    public function calculateTotal(Collection $services): float
    {
        $totalPrice = 0;
        foreach ($services as $service) {
            $totalPrice += $service->getServicePrice();
        }
        return $totalPrice;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function __toString(): string
    {
        return $this->reference;
    }

}
