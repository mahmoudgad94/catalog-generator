<?php

namespace App\Repository;

use App\Entity\CatalogAccessPassword;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CatalogAccessPassword>
 */
class CatalogAccessPasswordRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CatalogAccessPassword::class);
    }
}
