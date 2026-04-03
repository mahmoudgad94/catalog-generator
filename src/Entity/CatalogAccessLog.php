<?php

namespace App\Entity;

use App\Repository\CatalogAccessLogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CatalogAccessLogRepository::class)]
#[ORM\Table(name: 'catalog_access_log')]
class CatalogAccessLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Catalog::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Catalog $catalog = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 45, nullable: true)]
    private ?string $ipAddress = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $accessedAt = null;

    public function __construct()
    {
        $this->accessedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getCatalog(): ?Catalog { return $this->catalog; }
    public function setCatalog(?Catalog $catalog): static { $this->catalog = $catalog; return $this; }
    public function getEmail(): ?string { return $this->email; }
    public function setEmail(?string $email): static { $this->email = $email; return $this; }
    public function getIpAddress(): ?string { return $this->ipAddress; }
    public function setIpAddress(?string $ipAddress): static { $this->ipAddress = $ipAddress; return $this; }
    public function getAccessedAt(): ?\DateTimeImmutable { return $this->accessedAt; }
    public function setAccessedAt(\DateTimeImmutable $accessedAt): static { $this->accessedAt = $accessedAt; return $this; }
}
