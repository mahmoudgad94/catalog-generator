<?php

namespace App\Entity;

use App\Enum\FilterOperator;
use App\Repository\CatalogFilterRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CatalogFilterRepository::class)]
#[ORM\Table(name: 'catalog_filter')]
class CatalogFilter
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Catalog::class, inversedBy: 'filters')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Catalog $catalog = null;

    #[ORM\Column(length: 255)]
    private ?string $field = null;

    #[ORM\Column(length: 50, enumType: FilterOperator::class)]
    private ?FilterOperator $operator = null;

    #[ORM\Column(type: Types::JSON)]
    private array $value = [];

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    private int $position = 0;

    public function getId(): ?int { return $this->id; }
    public function getCatalog(): ?Catalog { return $this->catalog; }
    public function setCatalog(?Catalog $catalog): static { $this->catalog = $catalog; return $this; }
    public function getField(): ?string { return $this->field; }
    public function setField(string $field): static { $this->field = $field; return $this; }
    public function getOperator(): ?FilterOperator { return $this->operator; }
    public function setOperator(FilterOperator $operator): static { $this->operator = $operator; return $this; }
    public function getValue(): array { return $this->value; }
    public function setValue(array $value): static { $this->value = $value; return $this; }
    public function getPosition(): int { return $this->position; }
    public function setPosition(int $position): static { $this->position = $position; return $this; }
}
