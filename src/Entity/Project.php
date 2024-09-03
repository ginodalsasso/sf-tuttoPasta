<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
class Project
{
    // ---------------------------------ATTRIBUTS--------------------------------- //
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\Column(length: 255)]
    private ?string $projectName = null;


    #[ORM\Column(type: Types::TEXT)]
    private ?string $projectContent = null;


    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $projectDate = null;


    /**
     * @var Collection<int, ProjectImg>
     */
    #[ORM\OneToMany(targetEntity: ProjectImg::class, mappedBy: 'project')]
    private Collection $images;


    /**
     * @var Collection<int, Category>
     */
    #[ORM\ManyToMany(targetEntity: Category::class, mappedBy: 'projects')]
    private Collection $categories;


    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $projectTitle = null;

    
    // ---------------------------------CONSTRUCT--------------------------------- //


    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->categories = new ArrayCollection();
    }

    // ---------------------------------GETTERS AND SETTERS--------------------------------- //


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProjectName(): ?string
    {
        return $this->projectName;
    }

    public function setProjectName(string $projectName): static
    {
        $this->projectName = $projectName;

        return $this;
    }

    public function getProjectTitle(): ?string
    {
        return $this->projectTitle;
    }

    public function setProjectTitle(?string $projectTitle): static
    {
        $this->projectTitle = $projectTitle;

        return $this;
    }

    public function getProjectContent(): ?string
    {
        return $this->projectContent;
    }

    public function setProjectContent(string $projectContent): static
    {
        $this->projectContent = $projectContent;

        return $this;
    }

    public function getProjectDate(): ?\DateTimeInterface
    {
        return $this->projectDate;
    }

    public function setProjectDate(\DateTimeInterface $projectDate): static
    {
        $this->projectDate = $projectDate;

        return $this;
    }

    /**
     * @return Collection<int, ProjectImg>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(ProjectImg $image): static
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setProject($this);
        }

        return $this;
    }

    public function removeImage(ProjectImg $image): static
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getProject() === $this) {
                $image->setProject(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
            $category->addProject($this);
        }

        return $this;
    }

    public function removeCategory(Category $category): static
    {
        if ($this->categories->removeElement($category)) {
            $category->removeProject($this);
        }

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function __toString()
    {
        return $this -> projectName;
    }


}