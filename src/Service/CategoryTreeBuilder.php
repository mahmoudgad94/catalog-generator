<?php

namespace App\Service;

use App\Entity\Category;

class CategoryTreeBuilder
{
    /**
     * Build a nested tree from a flat array of Category entities.
     * @param Category[] $categories Flat list, typically ordered by position
     * @return Category[] Root-level categories with children populated
     */
    public function build(array $categories): array
    {
        $indexed = [];
        foreach ($categories as $category) {
            $indexed[$category->getId()] = $category;
        }

        $roots = [];
        foreach ($categories as $category) {
            $parent = $category->getParent();
            if ($parent === null) {
                $roots[] = $category;
            }
        }

        return $roots;
    }
}
