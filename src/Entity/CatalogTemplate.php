<?php

namespace App\Entity;

use App\Repository\CatalogTemplateRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CatalogTemplateRepository::class)]
#[ORM\Table(name: 'catalog_template')]
class CatalogTemplate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $directory = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $thumbnail = null;

    public function getId(): ?int { return $this->id; }
    public function getName(): ?string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }
    public function getDirectory(): ?string { return $this->directory; }
    public function setDirectory(string $directory): static { $this->directory = $directory; return $this; }
    public function getThumbnail(): ?string { return $this->thumbnail; }
    public function setThumbnail(?string $thumbnail): static { $this->thumbnail = $thumbnail; return $this; }
    public function __toString(): string { return $this->name ?? ''; }
}
