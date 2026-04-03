<?php

namespace App\Entity;

use App\Enum\AccessMode;
use App\Repository\CatalogAccessRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CatalogAccessRepository::class)]
#[ORM\Table(name: 'catalog_access')]
class CatalogAccess
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Catalog::class, inversedBy: 'access')]
    #[ORM\JoinColumn(nullable: false, unique: true, onDelete: 'CASCADE')]
    private ?Catalog $catalog = null;

    #[ORM\Column(length: 20, enumType: AccessMode::class)]
    private AccessMode $mode = AccessMode::Public;

    /** @var Collection<int, CatalogAccessPassword> */
    #[ORM\OneToMany(targetEntity: CatalogAccessPassword::class, mappedBy: 'catalogAccess', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $passwords;

    public function __construct()
    {
        $this->passwords = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getCatalog(): ?Catalog { return $this->catalog; }
    public function setCatalog(?Catalog $catalog): static { $this->catalog = $catalog; return $this; }
    public function getMode(): AccessMode { return $this->mode; }
    public function setMode(AccessMode $mode): static { $this->mode = $mode; return $this; }

    /** @return Collection<int, CatalogAccessPassword> */
    public function getPasswords(): Collection { return $this->passwords; }
    public function addPassword(CatalogAccessPassword $password): static
    {
        if (!$this->passwords->contains($password)) {
            $this->passwords->add($password);
            $password->setCatalogAccess($this);
        }
        return $this;
    }
    public function removePassword(CatalogAccessPassword $password): static
    {
        if ($this->passwords->removeElement($password)) {
            if ($password->getCatalogAccess() === $this) { $password->setCatalogAccess(null); }
        }
        return $this;
    }
}
