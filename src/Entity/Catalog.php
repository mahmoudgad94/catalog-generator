<?php

namespace App\Entity;

use App\Repository\CatalogRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: CatalogRepository::class)]
#[ORM\Table(name: 'catalog')]
class Catalog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[Gedmo\Slug(fields: ['name'])]
    #[ORM\Column(length: 255, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(targetEntity: CatalogTemplate::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?CatalogTemplate $template = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $isPublished = false;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $updatedAt = null;

    /** @var Collection<int, CatalogFilter> */
    #[ORM\OneToMany(targetEntity: CatalogFilter::class, mappedBy: 'catalog', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $filters;

    /** @var Collection<int, CatalogSort> */
    #[ORM\OneToMany(targetEntity: CatalogSort::class, mappedBy: 'catalog', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $sorts;

    /** @var Collection<int, CatalogInputConfig> */
    #[ORM\OneToMany(targetEntity: CatalogInputConfig::class, mappedBy: 'catalog', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $inputConfigs;

    #[ORM\OneToOne(targetEntity: CatalogAccess::class, mappedBy: 'catalog', cascade: ['persist', 'remove'])]
    private ?CatalogAccess $access = null;

    public function __construct()
    {
        $this->filters = new ArrayCollection();
        $this->sorts = new ArrayCollection();
        $this->inputConfigs = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getName(): ?string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }

    public function getSlug(): ?string { return $this->slug; }
    public function setSlug(string $slug): static { $this->slug = $slug; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }

    public function getTemplate(): ?CatalogTemplate { return $this->template; }
    public function setTemplate(?CatalogTemplate $template): static { $this->template = $template; return $this; }

    public function isPublished(): bool { return $this->isPublished; }
    public function setIsPublished(bool $isPublished): static { $this->isPublished = $isPublished; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }

    /** @return Collection<int, CatalogFilter> */
    public function getFilters(): Collection { return $this->filters; }
    public function addFilter(CatalogFilter $filter): static
    {
        if (!$this->filters->contains($filter)) {
            $this->filters->add($filter);
            $filter->setCatalog($this);
        }
        return $this;
    }
    public function removeFilter(CatalogFilter $filter): static
    {
        if ($this->filters->removeElement($filter)) {
            if ($filter->getCatalog() === $this) { $filter->setCatalog(null); }
        }
        return $this;
    }

    /** @return Collection<int, CatalogSort> */
    public function getSorts(): Collection { return $this->sorts; }
    public function addSort(CatalogSort $sort): static
    {
        if (!$this->sorts->contains($sort)) {
            $this->sorts->add($sort);
            $sort->setCatalog($this);
        }
        return $this;
    }
    public function removeSort(CatalogSort $sort): static
    {
        if ($this->sorts->removeElement($sort)) {
            if ($sort->getCatalog() === $this) { $sort->setCatalog(null); }
        }
        return $this;
    }

    /** @return Collection<int, CatalogInputConfig> */
    public function getInputConfigs(): Collection { return $this->inputConfigs; }
    public function addInputConfig(CatalogInputConfig $config): static
    {
        if (!$this->inputConfigs->contains($config)) {
            $this->inputConfigs->add($config);
            $config->setCatalog($this);
        }
        return $this;
    }
    public function removeInputConfig(CatalogInputConfig $config): static
    {
        if ($this->inputConfigs->removeElement($config)) {
            if ($config->getCatalog() === $this) { $config->setCatalog(null); }
        }
        return $this;
    }

    public function getAccess(): ?CatalogAccess { return $this->access; }
    public function setAccess(?CatalogAccess $access): static
    {
        $this->access = $access;
        if ($access !== null && $access->getCatalog() !== $this) {
            $access->setCatalog($this);
        }
        return $this;
    }

    public function __toString(): string { return $this->name ?? ''; }
}
