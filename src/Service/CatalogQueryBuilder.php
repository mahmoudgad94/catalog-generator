<?php

namespace App\Service;

use App\Entity\Catalog;
use App\Enum\FilterOperator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class CatalogQueryBuilder
{
    public function __construct(private EntityManagerInterface $em) {}

    public function buildQuery(Catalog $catalog, array $userFilters = []): QueryBuilder
    {
        $qb = $this->em->createQueryBuilder()
            ->select('p')
            ->from('App\Entity\Product', 'p')
            ->leftJoin('p.categories', 'cat')
            ->leftJoin('p.tags', 'tag');

        // Apply saved catalog filters
        $paramIndex = 0;
        foreach ($catalog->getFilters() as $filter) {
            $paramName = 'filter_' . $paramIndex++;
            $value = $filter->getValue()[0] ?? '';

            match ($filter->getField()) {
                'name' => $this->applyStringFilter($qb, 'p.name', $filter->getOperator(), $value, $paramName),
                'price' => $this->applyNumericFilter($qb, 'p.price', $filter->getOperator(), $value, $paramName),
                'category' => $qb->andWhere('cat.id = :' . $paramName)->setParameter($paramName, (int) $value),
                'tag' => $qb->andWhere('tag.id = :' . $paramName)->setParameter($paramName, (int) $value),
                default => null,
            };
        }

        // Apply saved sorts
        $hasSorts = false;
        foreach ($catalog->getSorts() as $sort) {
            $field = match ($sort->getField()) {
                'name' => 'p.name',
                'price' => 'p.price',
                'created_at' => 'p.createdAt',
                'position' => 'p.position',
                default => null,
            };
            if ($field) {
                $qb->addOrderBy($field, $sort->getDirection());
                $hasSorts = true;
            }
        }

        if (!$hasSorts) {
            $qb->orderBy('p.createdAt', 'DESC');
        }

        // Apply user-submitted filters (from public frontend)
        if (!empty($userFilters['search'])) {
            $qb->andWhere('p.name LIKE :user_search OR p.description LIKE :user_search')
               ->setParameter('user_search', '%' . $userFilters['search'] . '%');
        }

        if (!empty($userFilters['category'])) {
            $qb->andWhere('cat.id = :user_category')
               ->setParameter('user_category', (int) $userFilters['category']);
        }

        if (!empty($userFilters['tag'])) {
            $qb->andWhere('tag.id = :user_tag')
               ->setParameter('user_tag', (int) $userFilters['tag']);
        }

        if (!empty($userFilters['price_min'])) {
            $qb->andWhere('p.price >= :user_price_min')
               ->setParameter('user_price_min', $userFilters['price_min']);
        }

        if (!empty($userFilters['price_max'])) {
            $qb->andWhere('p.price <= :user_price_max')
               ->setParameter('user_price_max', $userFilters['price_max']);
        }

        if (!empty($userFilters['sort'])) {
            $parts = explode('_', $userFilters['sort'], 2);
            if (count($parts) === 2) {
                $sortField = match ($parts[0]) {
                    'name' => 'p.name',
                    'price' => 'p.price',
                    'date' => 'p.createdAt',
                    default => null,
                };
                if ($sortField) {
                    $qb->orderBy($sortField, $parts[1] === 'desc' ? 'DESC' : 'ASC');
                }
            }
        }

        $qb->groupBy('p.id');

        return $qb;
    }

    private function applyStringFilter(QueryBuilder $qb, string $field, FilterOperator $op, string $value, string $param): void
    {
        match ($op) {
            FilterOperator::Eq => $qb->andWhere("$field = :$param")->setParameter($param, $value),
            FilterOperator::Neq => $qb->andWhere("$field != :$param")->setParameter($param, $value),
            FilterOperator::Like => $qb->andWhere("$field LIKE :$param")->setParameter($param, '%' . $value . '%'),
            default => null,
        };
    }

    private function applyNumericFilter(QueryBuilder $qb, string $field, FilterOperator $op, string $value, string $param): void
    {
        $numValue = (float) $value;
        match ($op) {
            FilterOperator::Eq => $qb->andWhere("$field = :$param")->setParameter($param, $numValue),
            FilterOperator::Neq => $qb->andWhere("$field != :$param")->setParameter($param, $numValue),
            FilterOperator::Gt => $qb->andWhere("$field > :$param")->setParameter($param, $numValue),
            FilterOperator::Lt => $qb->andWhere("$field < :$param")->setParameter($param, $numValue),
            FilterOperator::Gte => $qb->andWhere("$field >= :$param")->setParameter($param, $numValue),
            FilterOperator::Lte => $qb->andWhere("$field <= :$param")->setParameter($param, $numValue),
            default => null,
        };
    }
}
