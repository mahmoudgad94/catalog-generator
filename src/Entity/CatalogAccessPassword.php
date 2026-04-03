<?php

namespace App\Entity;

use App\Repository\CatalogAccessPasswordRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CatalogAccessPasswordRepository::class)]
#[ORM\Table(name: 'catalog_access_password')]
class CatalogAccessPassword
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: CatalogAccess::class, inversedBy: 'passwords')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?CatalogAccess $catalogAccess = null;

    #[ORM\Column(length: 255)]
    private ?string $passwordHash = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $label = null;

    public function getId(): ?int { return $this->id; }
    public function getCatalogAccess(): ?CatalogAccess { return $this->catalogAccess; }
    public function setCatalogAccess(?CatalogAccess $catalogAccess): static { $this->catalogAccess = $catalogAccess; return $this; }
    public function getPasswordHash(): ?string { return $this->passwordHash; }
    public function setPasswordHash(string $passwordHash): static { $this->passwordHash = $passwordHash; return $this; }
    public function getLabel(): ?string { return $this->label; }
    public function setLabel(?string $label): static { $this->label = $label; return $this; }
}
