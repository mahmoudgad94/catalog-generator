<?php

namespace App\Entity;

use App\Repository\CatalogInputConfigRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CatalogInputConfigRepository::class)]
#[ORM\Table(name: 'catalog_input_config')]
class CatalogInputConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Catalog::class, inversedBy: 'inputConfigs')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Catalog $catalog = null;

    #[ORM\Column(length: 50)]
    private ?string $inputType = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $label = null;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    private int $position = 0;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $options = null;

    public function getId(): ?int { return $this->id; }
    public function getCatalog(): ?Catalog { return $this->catalog; }
    public function setCatalog(?Catalog $catalog): static { $this->catalog = $catalog; return $this; }
    public function getInputType(): ?string { return $this->inputType; }
    public function setInputType(string $inputType): static { $this->inputType = $inputType; return $this; }
    public function getLabel(): ?string { return $this->label; }
    public function setLabel(?string $label): static { $this->label = $label; return $this; }
    public function getPosition(): int { return $this->position; }
    public function setPosition(int $position): static { $this->position = $position; return $this; }
    public function getOptions(): ?array { return $this->options; }
    public function setOptions(?array $options): static { $this->options = $options; return $this; }
}
