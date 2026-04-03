<?php

namespace App\Entity;

use App\Repository\CatalogSortRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CatalogSortRepository::class)]
#[ORM\Table(name: 'catalog_sort')]
class CatalogSort
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Catalog::class, inversedBy: 'sorts')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Catalog $catalog = null;

    #[ORM\Column(length: 255)]
    private ?string $field = null;

    #[ORM\Column(length: 4)]
    private ?string $direction = 'asc';

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    private int $position = 0;

    public function getId(): ?int { return $this->id; }
    public function getCatalog(): ?Catalog { return $this->catalog; }
    public function setCatalog(?Catalog $catalog): static { $this->catalog = $catalog; return $this; }
    public function getField(): ?string { return $this->field; }
    public function setField(string $field): static { $this->field = $field; return $this; }
    public function getDirection(): ?string { return $this->direction; }
    public function setDirection(string $direction): static { $this->direction = $direction; return $this; }
    public function getPosition(): int { return $this->position; }
    public function setPosition(int $position): static { $this->position = $position; return $this; }
}
